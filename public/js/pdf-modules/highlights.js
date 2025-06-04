// PDF Reader Highlights Module
// Handles text selection and highlighting

const HighlightManager = (function() {
    // State variables
    let currentSelection = null;
    let highlights = [];
    let highlightColors = {
        default: 'rgba(255, 255, 0, 0.3)',  // Yellow
        important: 'rgba(255, 0, 0, 0.3)',  // Red
        note: 'rgba(0, 255, 0, 0.3)'        // Green
    };

    // DOM elements
    let highlightTooltip;

    // Initialize the module
    function init() {
        createHighlightTooltip();

        // Try multiple times to inject the text selection handler
        // as PDF.js might take time to fully initialize
        let attempts = 0;
        const maxAttempts = 10; // Increase max attempts

        function tryInject() {
            attempts++;
            console.log(`Attempt ${attempts} to inject text selection handler...`);

            const success = injectTextSelectionHandler();

            // Check if the injection was successful
            if (success) {
                console.log('Text selection handler successfully injected');
                // Load highlights after successful injection
                setTimeout(() => {
                    loadHighlights();
                }, 500);
            } else if (attempts < maxAttempts) {
                console.log(`Attempt ${attempts} to inject text selection handler failed, trying again...`);
                // Increase the delay between attempts
                setTimeout(tryInject, 2000);
            } else {
                console.error('Failed to inject text selection handler after multiple attempts');
            }
        }

        // Start the injection process with a delay to ensure PDF.js is loaded
        setTimeout(tryInject, 2000);

        registerEventListeners();
    }

    // Create the highlight tooltip that appears when text is selected
    function createHighlightTooltip() {
        // Create tooltip element if it doesn't exist
        if (!document.getElementById('highlight-tooltip')) {
            highlightTooltip = document.createElement('div');
            highlightTooltip.id = 'highlight-tooltip';
            highlightTooltip.className = 'highlight-tooltip hidden';

            // Add highlight buttons
            highlightTooltip.innerHTML = `
                <button class="highlight-btn default" title="Highlight">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18">
                        <path fill="currentColor" d="M12.5 1L8 9h9l-4.5 8L8 9H1l6-4.5L4 1l5 3.5L14 1l-3 4.5L17 9h-5l4.5-8z"/>
                    </svg>
                </button>
                <button class="highlight-btn important" title="Important">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18">
                        <path fill="currentColor" d="M12 2L4 20h16L12 2zm0 5l4.5 9h-9L12 7z"/>
                    </svg>
                </button>
                <button class="highlight-btn note" title="Note">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18">
                        <path fill="currentColor" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                    </svg>
                </button>
            `;

            document.body.appendChild(highlightTooltip);
        } else {
            highlightTooltip = document.getElementById('highlight-tooltip');
        }
    }

    // Register event listeners
    function registerEventListeners() {
        // Add click event listeners to highlight buttons
        document.querySelectorAll('.highlight-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.classList.contains('important') ? 'important' :
                             this.classList.contains('note') ? 'note' : 'default';
                createHighlight(type);
            });
        });

        // Hide tooltip when clicking outside
        document.addEventListener('mousedown', function(e) {
            if (!highlightTooltip.contains(e.target) && !e.target.closest('.pdf-highlight')) {
                highlightTooltip.classList.add('hidden');
            }
        });

        // Listen for messages from the iframe
        window.addEventListener('message', function(e) {
            const pdfViewer = document.getElementById('pdf-viewer');
            if (e.source !== pdfViewer.contentWindow) return;

            const message = e.data;

            if (message && message.type === 'textSelection') {
                // Show tooltip near the selection
                const rect = message.rect;
                const viewerRect = pdfViewer.getBoundingClientRect();

                // Position tooltip near the selection
                const tooltipX = viewerRect.left + rect.left + (rect.width / 2);
                const tooltipY = viewerRect.top + rect.bottom + 10;

                highlightTooltip.style.left = tooltipX + 'px';
                highlightTooltip.style.top = tooltipY + 'px';
                highlightTooltip.classList.remove('hidden');

                // Store selection data
                currentSelection = {
                    text: message.selection,
                    page: message.page,
                    position: {
                        left: rect.left,
                        top: rect.top,
                        width: rect.width,
                        height: rect.height
                    },
                    rects: message.rects || []
                };

                console.log('Text selected:', message.selection);
                console.log('Selection rects:', message.rects);
            } else if (message && message.type === 'selectionCleared') {
                // Hide tooltip when selection is cleared
                setTimeout(() => {
                    if (!highlightTooltip.matches(':hover')) {
                        highlightTooltip.classList.add('hidden');
                    }
                }, 100);
                currentSelection = null;
            }
        });
    }

    // Create a highlight from the current selection
    function createHighlight(type = 'default') {
        if (!currentSelection) {
            console.error('No text selected');
            return;
        }

        try {
            const pdfViewer = document.getElementById('pdf-viewer');
            const frameWindow = pdfViewer.contentWindow;
            if (!frameWindow || !frameWindow.PDFViewerApplication) {
                console.error('PDF viewer not initialized');
                return;
            }

            const PDFViewerApplication = frameWindow.PDFViewerApplication;
            const currentPage = currentSelection.page || PDFViewerApplication.page;

            // Log the selection data for debugging
            console.log('Creating highlight with text:', currentSelection.text);
            console.log('Position:', currentSelection.position);
            console.log('Rects count:', currentSelection.rects ? currentSelection.rects.length : 0);

            // Create highlight object
            const highlight = {
                id: 'highlight-' + Date.now(),
                text: currentSelection.text,
                page: currentPage,
                position: currentSelection.position,
                rects: currentSelection.rects || [],
                type: type,
                color: highlightColors[type],
                timestamp: new Date().toISOString()
            };

            // Add to highlights array
            highlights.push(highlight);

            // Apply the highlight to the PDF
            applyHighlightToPdf(highlight);

            // Save highlights to localStorage
            saveHighlights();

            // Hide tooltip
            highlightTooltip.classList.add('hidden');

        } catch (err) {
            console.error('Error creating highlight:', err);
        }
    }

    // Apply a highlight to the PDF
    function applyHighlightToPdf(highlight) {
        const pdfViewer = document.getElementById('pdf-viewer');
        const frameWindow = pdfViewer.contentWindow;

        if (!frameWindow || !frameWindow.PDFViewerApplication) {
            console.error('PDF viewer not initialized');
            return;
        }

        console.log('Applying highlight to page', highlight.page, 'with rects:', highlight.rects.length);

        // Create a script to inject into the iframe
        const script = frameWindow.document.createElement('script');
        script.textContent = `
            (function() {
                try {
                    const highlightId = "${highlight.id}";
                    const page = ${highlight.page};
                    const position = ${JSON.stringify(highlight.position)};
                    const rects = ${JSON.stringify(highlight.rects || [])};
                    const color = "${highlight.color}";

                    console.log('Creating highlight with ID:', highlightId, 'on page:', page);

                    // Find the text layer for the current page
                    const textLayer = document.querySelector('.page[data-page-number="' + page + '"] .textLayer');

                    if (!textLayer) {
                        console.error('Text layer not found for page ' + page);
                        return;
                    }

                    console.log('Found text layer:', textLayer);

                    // Clear any existing highlights with this ID to prevent duplicates
                    document.querySelectorAll('.pdf-highlight[data-highlight-id="' + highlightId + '"]').forEach(el => el.remove());

                    // If we have rects from the selection, use those for more accurate highlighting
                    if (rects && rects.length > 0) {
                        console.log('Creating highlight from', rects.length, 'rects');

                        rects.forEach((rect, index) => {
                            // Create highlight element for each rect
                            const highlightElement = document.createElement('div');
                            highlightElement.className = 'pdf-highlight';
                            highlightElement.dataset.highlightId = highlightId;
                            highlightElement.dataset.rectIndex = index;

                            // Ensure we have good positioning with important rules
                            Object.assign(highlightElement.style, {
                                position: 'absolute',
                                left: rect.left + 'px',
                                top: rect.top + 'px',
                                width: rect.width + 'px',
                                height: rect.height + 'px',
                                backgroundColor: color,
                                pointerEvents: 'none',
                                zIndex: '1',
                                mixBlendMode: 'multiply',
                                boxShadow: 'none',
                                borderRadius: '2px',
                                opacity: '0.7'
                            });

                            // Add to text layer
                            textLayer.appendChild(highlightElement);
                            console.log('Added highlight rect', index, 'at', rect.left, rect.top);
                        });
                    } else {
                        // Fallback to single highlight if no rects
                        console.log('Creating single highlight at', position.left, position.top);

                        const highlightElement = document.createElement('div');
                        highlightElement.className = 'pdf-highlight';
                        highlightElement.dataset.highlightId = highlightId;

                        // Ensure we have good positioning with important rules
                        Object.assign(highlightElement.style, {
                            position: 'absolute',
                            left: position.left + 'px',
                            top: position.top + 'px',
                            width: position.width + 'px',
                            height: position.height + 'px',
                            backgroundColor: color,
                            pointerEvents: 'none',
                            zIndex: '1',
                            mixBlendMode: 'multiply',
                            boxShadow: 'none',
                            borderRadius: '2px',
                            opacity: '0.7'
                        });

                        // Add to text layer
                        textLayer.appendChild(highlightElement);
                    }
                } catch (e) {
                    console.error('Error applying highlight:', e);
                }
            })();
        `;

        frameWindow.document.body.appendChild(script);

        // Remove the script element after execution to keep the DOM clean
        setTimeout(() => {
            if (script && script.parentNode) {
                script.parentNode.removeChild(script);
            }
        }, 100);
    }

    // Inject text selection handler into the PDF.js iframe
    function injectTextSelectionHandler() {
        const pdfViewer = document.getElementById('pdf-viewer');

        // If no viewer exists yet, return
        if (!pdfViewer) {
            console.log('PDF viewer element not found');
            return false;
        }

        try {
            const frameWindow = pdfViewer.contentWindow;

            // Check if window has access to the iframe content
            if (!frameWindow || !frameWindow.document) {
                console.log('Cannot access iframe content - might be due to cross-origin restrictions');
                return false;
            }

            // If the PDF viewer isn't loaded yet, return false to try again later
            if (!frameWindow.PDFViewerApplication ||
                !frameWindow.PDFViewerApplication.initialized ||
                !frameWindow.PDFViewerApplication.pdfViewer ||
                !frameWindow.PDFViewerApplication.pdfViewer.currentPageNumber) {
                console.log('PDF.js not fully initialized yet');
                return false;
            }

            // If already injected, don't do it again
            if (frameWindow.textSelectionHandlerInjected) {
                console.log('Text selection handler already injected');
                return true;
            }

            console.log('PDF.js is ready, injecting text selection handler');

            // Add CSS for highlights to the iframe
            const style = frameWindow.document.createElement('style');
            style.textContent = `
                .pdf-highlight {
                    position: absolute !important;
                    pointer-events: none !important;
                    z-index: 1 !important;
                    mix-blend-mode: multiply !important;
                    box-shadow: none !important;
                    border-radius: 2px !important;
                    opacity: 0.7 !important;
                }

                .textLayer {
                    opacity: 0.25 !important;
                    user-select: text !important;
                    pointer-events: auto !important;
                    z-index: 2 !important;
                }

                .textLayer > span {
                    cursor: text !important;
                    color: transparent !important;
                    background: transparent !important;
                    position: absolute !important;
                    white-space: pre !important;
                    transform-origin: 0% 0% !important;
                    text-shadow: none !important;
                    box-shadow: none !important;
                }

                .textLayer ::selection {
                    background: rgba(0, 0, 255, 0.3) !important;
                }
            `;
            frameWindow.document.head.appendChild(style);

            // Set a global flag to track injection
            frameWindow.textSelectionHandlerInjected = true;

            // Inject selection script
            const script = frameWindow.document.createElement('script');
            script.textContent = `
                (function() {
                    if (window.textSelectionHandlerInjected) return true;
                    window.textSelectionHandlerInjected = true;
                    console.log("Text selection handler injected into PDF.js");

                    // Function to accurately get text from selection
                    function getAccurateSelectedText(selection) {
                        if (!selection || !selection.rangeCount) return '';

                        // Get the range
                        const range = selection.getRangeAt(0);

                        // First try the standard selection text
                        let text = selection.toString().trim();

                        // If selection text seems incomplete, try to extract from DOM
                        if (text.length < 2) {
                            try {
                                // Get all text nodes within the range
                                const iterator = document.createNodeIterator(
                                    range.commonAncestorContainer,
                                    NodeFilter.SHOW_TEXT,
                                    node => range.intersectsNode(node) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT
                                );

                                let textNodes = [];
                                let currentNode;
                                while (currentNode = iterator.nextNode()) {
                                    textNodes.push(currentNode);
                                }

                                // Extract text from text nodes
                                if (textNodes.length > 0) {
                                    text = textNodes.map(node => node.textContent).join('').trim();
                                }
                            } catch (e) {
                                console.error('Error extracting text from nodes:', e);
                            }
                        }

                        console.log('Selected text:', text);
                        return text;
                    }

                    // Listen for text selection
                    document.addEventListener('mouseup', function(e) {
                        const selection = window.getSelection();

                        // Check if there's an actual selection
                        if (selection && selection.rangeCount > 0 && selection.toString().trim().length > 0) {
                            try {
                                // Get the current page number
                                const currentPage = PDFViewerApplication.page;

                                // Get the accurate selected text
                                const selectedText = getAccurateSelectedText(selection);
                                if (!selectedText) {
                                    console.log('No valid text selected');
                                    return;
                                }

                                // Get the range for positioning
                                const range = selection.getRangeAt(0);

                                // Get selection rectangles
                                const rects = Array.from(range.getClientRects()).map(rect => ({
                                    left: Math.round(rect.left),
                                    top: Math.round(rect.top),
                                    width: Math.round(rect.width),
                                    height: Math.round(rect.height)
                                }));

                                // Get bounding rect for the tooltip positioning
                                const boundingRect = range.getBoundingClientRect();

                                // Send selection to parent window
                                window.parent.postMessage({
                                    type: 'textSelection',
                                    selection: selectedText,
                                    page: currentPage,
                                    rect: {
                                        left: Math.round(boundingRect.left),
                                        top: Math.round(boundingRect.top),
                                        width: Math.round(boundingRect.width),
                                        height: Math.round(boundingRect.height),
                                        bottom: Math.round(boundingRect.bottom),
                                        right: Math.round(boundingRect.right)
                                    },
                                    rects: rects
                                }, '*');
                            } catch (err) {
                                console.error('Error handling selection:', err);
                            }
                        } else {
                            // Selection was cleared, notify parent
                            window.parent.postMessage({
                                type: 'selectionCleared'
                            }, '*');
                        }
                    });

                    // Also listen for clicks that might clear selection
                    document.addEventListener('mousedown', function(e) {
                        // Only consider clicks that are not on text
                        if (e.target.nodeName !== 'SPAN' && e.target.nodeName !== 'DIV' && !e.target.closest('.textLayer')) {
                            window.parent.postMessage({
                                type: 'selectionCleared'
                            }, '*');
                        }
                    });

                    return true;
                })();
            `;

            frameWindow.document.body.appendChild(script);

            // Remove the script after execution
            setTimeout(() => {
                if (script && script.parentNode) {
                    script.parentNode.removeChild(script);
                }
            }, 100);

            return true;
        } catch (err) {
            console.error('Error injecting text selection handler:', err);
            return false;
        }
    }

    // Save highlights to localStorage
    function saveHighlights() {
        try {
            localStorage.setItem('pdf-highlights', JSON.stringify(highlights));
        } catch (e) {
            console.error('Error saving highlights:', e);
        }
    }

    // Load highlights from localStorage
    function loadHighlights() {
        try {
            const savedHighlights = localStorage.getItem('pdf-highlights');
            if (savedHighlights) {
                highlights = JSON.parse(savedHighlights);

                // Apply all highlights
                highlights.forEach(highlight => {
                    applyHighlightToPdf(highlight);
                });
            }
        } catch (e) {
            console.error('Error loading highlights:', e);
        }
    }

    // Remove a highlight
    function removeHighlight(highlightId) {
        // Find the highlight in the array
        const index = highlights.findIndex(h => h.id === highlightId);
        if (index !== -1) {
            highlights.splice(index, 1);

            // Remove from the DOM
            const pdfViewer = document.getElementById('pdf-viewer');
            const frameWindow = pdfViewer.contentWindow;

            if (frameWindow) {
                const highlightElements = frameWindow.document.querySelectorAll(`.pdf-highlight[data-highlight-id="${highlightId}"]`);
                highlightElements.forEach(el => el.remove());
            }

            // Save updated highlights
            saveHighlights();
        }
    }

    // Clear all highlights
    function clearAllHighlights() {
        highlights = [];

        // Remove all highlight elements
        const pdfViewer = document.getElementById('pdf-viewer');
        const frameWindow = pdfViewer.contentWindow;

        if (frameWindow) {
            const highlightElements = frameWindow.document.querySelectorAll('.pdf-highlight');
            highlightElements.forEach(el => el.remove());
        }

        // Save updated highlights
        saveHighlights();
    }

    // Public API
    return {
        init,
        createHighlight,
        removeHighlight,
        clearAllHighlights,
        getHighlights: function() {
            return highlights;
        }
    };
})();
