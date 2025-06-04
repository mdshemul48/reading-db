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
            if (position === 'bottom') {
                pageContainer.classList.add('page-entering-bottom');
            } else if (position === 'top') {
                pageContainer.classList.add('page-entering-top');
            }

            // Create canvas for this page
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            pageContainer.appendChild(canvas);

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
                viewport: viewport
            };

            const renderTask = page.render(renderContext);

            // Wait for rendering to finish
            renderTask.promise.then(() => {
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