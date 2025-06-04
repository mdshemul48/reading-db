// PDF Renderer Module
// Handles page rendering and zoom operations

const PageRenderer = (function() {
    function loadInitialPages() {
        const state = PDFCore.getState();
        const elements = PDFCore.init();

        // Clear container
        elements.pdfContainer.innerHTML = '';

        // Render initial page and preload a few more
        const pagesToPreload = Math.min(3, state.pdfDoc.numPages);

        for (let i = state.pageNum; i < state.pageNum + pagesToPreload; i++) {
            renderPage(i, i === state.pageNum ? 'initial' : 'bottom');
        }

        // Setup scroll observer
        setupScrollObserver();
    }

    function setupScrollObserver() {
        const state = PDFCore.getState();
        const elements = PDFCore.init();

        const options = {
            root: elements.pdfScrollContainer,
            rootMargin: '0px',
            threshold: 0.5
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const pageId = entry.target.dataset.pageNumber;
                    if (pageId) {
                        const currentViewablePageNum = parseInt(pageId);
                        PDFCore.setState({ currentViewablePageNum });
                        elements.currentPageEl.textContent = currentViewablePageNum;
                        document.getElementById('viewable-page').textContent = currentViewablePageNum;
                        ProgressManager.updateProgress();
                        ProgressManager.saveProgress();
                    }
                }
            });
        }, options);

        // Observe each page
        document.querySelectorAll('.pdf-page').forEach(page => {
            observer.observe(page);
        });

        // Store the observer to reuse later
        window.pageObserver = observer;
    }

    function renderPage(num, position = 'bottom') {
        const state = PDFCore.getState();
        const elements = PDFCore.init();

        if (state.renderedPages.has(num)) return;

        state.renderedPages.add(num);
        PDFCore.setState({ renderedPages: state.renderedPages });

        // Add loading indicator
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'page-loading';
        loadingDiv.id = `loading-${num}`;
        loadingDiv.innerHTML = '<div class="page-loading-spinner"></div>';

        if (position === 'top') {
            elements.pdfContainer.insertBefore(loadingDiv, elements.pdfContainer.firstChild);
        } else {
            elements.pdfContainer.appendChild(loadingDiv);
        }

        // Using Promise to fetch the page
        state.pdfDoc.getPage(num).then((page) => {
            const viewport = page.getViewport({
                scale: state.scale
            });

            // Create container for this page
            const pageContainer = document.createElement('div');
            pageContainer.className = 'pdf-page';
            pageContainer.dataset.pageNumber = num;
            pageContainer.style.width = `${viewport.width}px`;
            pageContainer.style.height = `${viewport.height}px`;

            if (position === 'bottom') {
                pageContainer.classList.add('page-entering-bottom');
            } else if (position === 'top') {
                pageContainer.classList.add('page-entering-top');
            }

            // Create canvas wrapper and canvas for this page
            const canvasWrapper = document.createElement('div');
            canvasWrapper.className = 'canvasWrapper';
            canvasWrapper.style.width = `${viewport.width}px`;
            canvasWrapper.style.height = `${viewport.height}px`;

            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', { alpha: false }); // Disable alpha for better performance
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            canvas.style.width = '100%';
            canvas.style.height = '100%';

            canvasWrapper.appendChild(canvas);
            pageContainer.appendChild(canvasWrapper);

            // Create text layer div
            const textLayerDiv = document.createElement('div');
            textLayerDiv.className = 'textLayer';
            textLayerDiv.style.width = `${viewport.width}px`;
            textLayerDiv.style.height = `${viewport.height}px`;
            textLayerDiv.style.position = 'absolute';
            textLayerDiv.style.left = '0';
            textLayerDiv.style.top = '0';
            textLayerDiv.style.right = '0';
            textLayerDiv.style.bottom = '0';
            textLayerDiv.style.zIndex = '2';
            textLayerDiv.style.setProperty('--scale-factor', state.scale.toString());
            pageContainer.appendChild(textLayerDiv);

            // Store reference to canvas
            state.pagesCanvases[num] = canvas;
            PDFCore.setState({ pagesCanvases: state.pagesCanvases });

            // Replace loading indicator with page
            const loadingIndicator = document.getElementById(`loading-${num}`);
            if (position === 'top') {
                elements.pdfContainer.insertBefore(pageContainer, loadingIndicator);
            } else {
                elements.pdfContainer.insertBefore(pageContainer, loadingIndicator.nextSibling);
            }
            loadingIndicator.remove();

            // Render PDF page into canvas context
            const renderContext = {
                canvasContext: ctx,
                viewport: viewport,
                // Enable text rendering for better quality
                renderInteractiveForms: true,
                enableWebGL: false
            };

            const renderTask = page.render(renderContext);

            // Wait for rendering to finish
            renderTask.promise.then(() => {
                // Get text content with improved settings for better text extraction
                return page.getTextContent({
                    normalizeWhitespace: false, // Don't normalize whitespace to preserve text positioning
                    disableCombineTextItems: true, // Don't combine text items to keep original positions
                    includeMarkedContent: true // Include marked content for better text recognition
                });
            }).then((textContent) => {
                // Render text layer with improved options
                const textLayer = pdfjsLib.renderTextLayer({
                    textContentSource: textContent,
                    container: textLayerDiv,
                    viewport: viewport,
                    textDivs: [],
                    enhanceTextSelection: true // Enable enhanced text selection
                });

                return textLayer.promise;
            }).then(() => {
                // Ensure text is properly positioned
                Array.from(textLayerDiv.children).forEach(span => {
                    // Remove any shadows from spans
                    span.style.textShadow = 'none';
                    span.style.boxShadow = 'none';
                });

                // Update page positions for navigation
                updatePagePositions();

                // Observe this page for visibility
                if (window.pageObserver) {
                    window.pageObserver.observe(pageContainer);
                }

                PDFCore.setState({ isLoadingNewPage: false });

                // If this was the first page, scroll to it
                if (position === 'initial') {
                    setTimeout(() => {
                        pageContainer.scrollIntoView();
                    }, 100);
                }
            }).catch(err => {
                console.error('Error rendering text layer:', err);
            });
        });
    }

    function updatePagePositions() {
        const state = PDFCore.getState();
        const pagePositions = state.pagePositions;

        document.querySelectorAll('.pdf-page').forEach(page => {
            const pageNum = parseInt(page.dataset.pageNumber);
            pagePositions[pageNum] = page.offsetTop;
        });

        PDFCore.setState({ pagePositions });
    }

    function handleZoomChange() {
        const state = PDFCore.getState();
        const elements = PDFCore.init();

        // Save zoom level to localStorage
        localStorage.setItem('pdf-zoom-level', state.scale.toString());

        // Store current scroll position ratio
        const container = elements.pdfScrollContainer;
        const scrollRatio = container.scrollTop / container.scrollHeight;

        // Clear all pages
        elements.pdfContainer.innerHTML = '';
        state.renderedPages.clear();
        PDFCore.setState({ renderedPages: new Set() });

        // Re-render visible and surrounding pages
        const pagesToRender = [state.currentViewablePageNum];
        if (state.currentViewablePageNum > 1) pagesToRender.unshift(state.currentViewablePageNum - 1);
        if (state.currentViewablePageNum < state.pdfDoc.numPages) pagesToRender.push(state.currentViewablePageNum + 1);

        // Render pages
        pagesToRender.forEach(pageNum => {
            renderPage(pageNum);
        });

        // Restore scroll position
        setTimeout(() => {
            container.scrollTop = scrollRatio * container.scrollHeight;
        }, 100);
    }

    return {
        loadInitialPages,
        setupScrollObserver,
        renderPage,
        updatePagePositions,
        handleZoomChange
    };
})();
