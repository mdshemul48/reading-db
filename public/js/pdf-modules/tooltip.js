/**
 * PDF Text Selection Tooltip Module
 * Handles the tooltip UI and actions when text is selected in the PDF viewer
 */
const TooltipManager = (() => {
    // DOM elements
    let tooltip = null;
    let selectedText = '';
    let currentRange = null;
    let tooltipVisible = false;
    let debugMode = true; // Enable debug mode

    // Debug function
    const debug = (message, obj = null) => {
        if (debugMode) {
            if (obj) {
                console.log(`[TooltipManager] ${message}`, obj);
            } else {
                console.log(`[TooltipManager] ${message}`);
            }
        }
    };

    // Initialize the tooltip module
    const init = () => {
        debug('Initializing TooltipManager');
        createTooltipElement();
        registerEventListeners();

        // Listen for the custom textselected event
        document.addEventListener('textselected', (e) => {
            debug('Custom textselected event received', e.detail);
            if (e.detail && e.detail.selection) {
                handleCustomSelection(e.detail.selection, e);
            }
        });

        // Listen for the textlayerrendered event
        document.addEventListener('textlayerrendered', (e) => {
            debug('Text layer rendered event', e.detail);
            if (e.detail && e.detail.textLayer) {
                // Add direct event handlers to the new text layer
                attachTextLayerHandlers(e.detail.textLayer);
            }
        });

        // Direct global selection handler - this will catch all text selections
        document.addEventListener('selectionchange', function() {
            const selection = window.getSelection();
            const text = selection.toString().trim();

            if (text) {
                debug('Global selectionchange event triggered with text:', text);

                // Use a small delay to let the selection complete
                setTimeout(() => {
                    try {
                        if (selection.rangeCount > 0) {
                            selectedText = text;
                            currentRange = selection.getRangeAt(0);
                            const rect = currentRange.getBoundingClientRect();

                            if (rect.width > 0 && rect.height > 0) {
                                positionTooltip(rect);
                                showTooltip();
                                debug('Tooltip shown from global selection handler');
                            }
                        }
                    } catch (err) {
                        debug('Error in global selectionchange handler', err);
                    }
                }, 10);
            }
        });

        // Add a test method to window to allow manual tooltip testing
        window.showPdfTooltip = function(text, x, y) {
            forceShowTooltip(text, x, y);
        };
    };

    // Attach handlers to a text layer
    const attachTextLayerHandlers = (textLayer) => {
        debug('Attaching handlers to text layer', textLayer);

        // Add direct handler to the text layer
        textLayer.addEventListener('mouseup', (e) => {
            debug('Mouseup from attached handler on text layer');
            const selection = window.getSelection();
            if (selection && selection.toString().trim()) {
                handleCustomSelection(selection, e);
            }
        });

        // Add handlers to all spans within the text layer
        const spans = textLayer.querySelectorAll('span');
        debug(`Attaching handlers to ${spans.length} spans`);

        spans.forEach(span => {
            span.addEventListener('mouseup', (e) => {
                debug('Mouseup on text span', span.textContent);
                const selection = window.getSelection();
                if (selection && selection.toString().trim()) {
                    handleCustomSelection(selection, e);
                }
            });
        });
    };

    // Handle a custom selection event
    const handleCustomSelection = (selection, e) => {
        selectedText = selection.toString().trim();
        debug('Custom selection handler', selectedText);

        if (selectedText) {
            try {
                // Get selection position
                currentRange = selection.getRangeAt(0);
                const rect = currentRange.getBoundingClientRect();

                debug('Selection rectangle', {
                    left: rect.left,
                    top: rect.top,
                    width: rect.width,
                    height: rect.height
                });

                // Only show tooltip if we have a valid rect with dimensions
                if (rect.width > 0 && rect.height > 0) {
                    // Position the tooltip above the selection
                    positionTooltip(rect);
                    showTooltip();
                    debug('Tooltip shown from custom selection handler');
                } else {
                    debug('Invalid selection rectangle dimensions');
                }
            } catch (err) {
                debug('Error handling custom selection', err);
            }
        }
    };

    // Create the tooltip element and append to body
    const createTooltipElement = () => {
        // Remove existing tooltip if any
        if (document.getElementById('pdf-text-tooltip')) {
            document.getElementById('pdf-text-tooltip').remove();
            debug('Removed existing tooltip');
        }

        tooltip = document.createElement('div');
        tooltip.id = 'pdf-text-tooltip';
        tooltip.className = 'pdf-text-tooltip';
        tooltip.innerHTML = `
            <button class="tooltip-btn highlight-btn" data-action="highlight">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                    <path d="M15.2 2.09L21 7.89V19c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2h10.2zm-1.4 3l-1.48.29-.7 1.43-.7-1.43-1.48-.29 1.06-1.08-.26-1.48 1.38.7L12 2.52l1.38.7-.26 1.48L14.6 5.81l-1.48.29-.7 1.43-.7-1.43z" fill="currentColor"/>
                </svg>
                <span>Highlight</span>
            </button>
            <button class="tooltip-btn copy-btn" data-action="copy">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                    <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z" fill="currentColor"/>
                </svg>
                <span>Copy</span>
            </button>
            <button class="tooltip-btn note-btn" data-action="note">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" fill="currentColor"/>
                </svg>
                <span>Add Note</span>
            </button>
            <button class="tooltip-btn search-btn" data-action="search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" fill="currentColor"/>
                </svg>
                <span>Search Web</span>
            </button>
        `;

        document.body.appendChild(tooltip);
        debug('Created tooltip element');

        // Add event listeners to tooltip buttons
        tooltip.querySelectorAll('.tooltip-btn').forEach(btn => {
            btn.addEventListener('click', handleTooltipAction);
        });
    };

    // Register event listeners for text selection
    const registerEventListeners = () => {
        debug('Registering event listeners');
        const pdfContainer = document.getElementById('pdf-container');

        if (!pdfContainer) {
            debug('ERROR: PDF container not found!');
            return;
        }

        // Add direct mouseup event to document
        document.addEventListener('mouseup', function(e) {
            debug('mouseup on document', e.target);
            handleTextSelection(e);
        });

        // Add direct mouseup to pdf-viewer
        const pdfViewer = document.getElementById('pdf-viewer');
        if (pdfViewer) {
            pdfViewer.addEventListener('mouseup', function(e) {
                debug('mouseup on pdf-viewer', e.target);
                handleTextSelection(e);
            });

            // Also attach to all existing textLayers
            const textLayers = pdfViewer.querySelectorAll('.textLayer');
            debug(`Found ${textLayers.length} existing textLayers`);
            textLayers.forEach(layer => {
                attachTextLayerHandlers(layer);
            });
        }

        // Hide tooltip when clicking outside
        document.addEventListener('click', (e) => {
            if (tooltipVisible && !tooltip.contains(e.target)) {
                hideTooltip();
            }
        });

        // Hide tooltip when scrolling
        document.addEventListener('scroll', () => {
            if (tooltipVisible) {
                hideTooltip();
            }
        });

        // Use a MutationObserver to watch for textLayer elements being added
        debug('Setting up MutationObserver');
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                    for (let i = 0; i < mutation.addedNodes.length; i++) {
                        const node = mutation.addedNodes[i];
                        if (node.classList && node.classList.contains('textLayer')) {
                            debug('New textLayer detected', node);
                            attachTextLayerHandlers(node);
                        }
                    }
                }
            });
        });

        observer.observe(pdfContainer, {
            childList: true,
            subtree: true
        });
    };

    // Handle text selection event
    const handleTextSelection = (e) => {
        // Don't use setTimeout - react immediately to selection
        const selection = window.getSelection();
        selectedText = selection.toString().trim();

        debug('Text selection handler called', {
            hasSelection: !!selectedText,
            length: selectedText.length,
            target: e.target
        });

        // Check if selection is within a textLayer or the PDF viewer
        const isInPdf = e.target.closest('#pdf-viewer') ||
                       e.target.closest('.textLayer') ||
                       e.target.classList.contains('textLayer') ||
                       (e.target.parentNode && e.target.parentNode.classList.contains('textLayer')) ||
                       e.target.getAttribute('role') === 'presentation';

        if (selectedText && isInPdf) {
            debug('Valid selection in PDF detected');
            try {
                // Get selection position
                currentRange = selection.getRangeAt(0);
                const rect = currentRange.getBoundingClientRect();

                debug('Selection rectangle', {
                    left: rect.left,
                    top: rect.top,
                    width: rect.width,
                    height: rect.height
                });

                // Only show tooltip if we have a valid rect with dimensions
                if (rect.width > 0 && rect.height > 0) {
                    // Position the tooltip above the selection
                    positionTooltip(rect);
                    showTooltip();
                    debug('Tooltip shown from handleTextSelection');
                } else {
                    debug('Invalid selection rectangle dimensions');
                }
            } catch (err) {
                debug('Error handling text selection', err);
            }
        } else if (tooltipVisible && !e.target.closest('#pdf-text-tooltip')) {
            hideTooltip();
        }
    };

    // Position the tooltip above the selection
    const positionTooltip = (rect) => {
        const tooltipWidth = tooltip.offsetWidth || 200;

        // Calculate position
        let left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
        let top = rect.top - 45; // Position above selection

        // Ensure tooltip stays within viewport
        if (left < 10) left = 10;
        if (left + tooltipWidth > window.innerWidth - 10) {
            left = window.innerWidth - tooltipWidth - 10;
        }

        // If tooltip would appear above viewport, position it below selection
        if (top < 10) {
            top = rect.bottom + 10;
        }

        tooltip.style.left = `${left}px`;
        tooltip.style.top = `${top}px`;

        debug('Positioned tooltip', { left, top });
    };

    // Show the tooltip
    const showTooltip = () => {
        tooltip.style.display = 'flex';
        tooltipVisible = true;
        debug('Showing tooltip');
    };

    // Hide the tooltip
    const hideTooltip = () => {
        tooltip.style.display = 'none';
        tooltipVisible = false;
        debug('Hiding tooltip');
    };

    // Force show the tooltip at a specific position (for external calls)
    const forceShowTooltip = (text, x, y) => {
        selectedText = text;
        tooltip.style.left = `${x}px`;
        tooltip.style.top = `${y}px`;
        showTooltip();
        debug('Force showing tooltip at', { x, y, text });
    };

    // Handle tooltip button actions
    const handleTooltipAction = (e) => {
        const action = e.currentTarget.getAttribute('data-action');
        debug('Tooltip action clicked', action);

        switch (action) {
            case 'highlight':
                // Use HighlightManager if available
                if (window.HighlightManager && typeof HighlightManager.addHighlight === 'function') {
                    HighlightManager.addHighlight(selectedText, currentRange);
                    debug('Added highlight');
                } else {
                    debug('HighlightManager not available');
                }
                break;

            case 'copy':
                // Copy selected text to clipboard
                navigator.clipboard.writeText(selectedText)
                    .then(() => {
                        showNotification('Text copied to clipboard');
                        debug('Text copied to clipboard');
                    })
                    .catch(err => {
                        debug('Failed to copy text', err);
                    });
                break;

            case 'note':
                // Open a note creation dialog
                showNoteDialog(selectedText);
                debug('Opened note dialog');
                break;

            case 'search':
                // Open a search query in a new tab
                const searchUrl = `https://www.google.com/search?q=${encodeURIComponent(selectedText)}`;
                window.open(searchUrl, '_blank');
                debug('Opened search', searchUrl);
                break;
        }

        hideTooltip();
    };

    // Show a temporary notification
    const showNotification = (message) => {
        const notification = document.createElement('div');
        notification.className = 'pdf-notification';
        notification.textContent = message;

        document.body.appendChild(notification);
        debug('Showing notification', message);

        // Remove after 2 seconds
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 1700);
    };

    // Show note dialog
    const showNoteDialog = (text) => {
        // Create note dialog if it doesn't exist
        let noteDialog = document.getElementById('pdf-note-dialog');

        if (!noteDialog) {
            noteDialog = document.createElement('div');
            noteDialog.id = 'pdf-note-dialog';
            noteDialog.className = 'pdf-dialog';
            noteDialog.innerHTML = `
                <div class="dialog-content">
                    <h3>Add Note</h3>
                    <div class="selected-text">"<span id="selected-text-preview"></span>"</div>
                    <textarea id="note-text" placeholder="Write your note here..."></textarea>
                    <div class="dialog-buttons">
                        <button id="cancel-note" class="dialog-btn cancel-btn">Cancel</button>
                        <button id="save-note" class="dialog-btn save-btn">Save Note</button>
                    </div>
                </div>
            `;

            document.body.appendChild(noteDialog);
            debug('Created note dialog');

            // Add event listeners
            document.getElementById('cancel-note').addEventListener('click', () => {
                noteDialog.style.display = 'none';
                debug('Note dialog canceled');
            });

            document.getElementById('save-note').addEventListener('click', () => {
                const noteText = document.getElementById('note-text').value;
                if (noteText.trim()) {
                    saveNote(selectedText, noteText);
                    noteDialog.style.display = 'none';
                    showNotification('Note saved successfully');
                    debug('Note saved', noteText);
                }
            });
        }

        // Update and show dialog
        document.getElementById('selected-text-preview').textContent =
            text.length > 100 ? text.substring(0, 97) + '...' : text;
        document.getElementById('note-text').value = '';
        noteDialog.style.display = 'flex';
    };

    // Save note (can be extended to save to database)
    const saveNote = (selectedText, noteText) => {
        // This can be extended to save to server/database
        debug('Saving note', { text: selectedText, note: noteText });

        // If we have an API endpoint for saving notes
        // Example:
        // fetch('/api/notes', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        //     },
        //     body: JSON.stringify({
        //         text: selectedText,
        //         note: noteText,
        //         page: window.PDFViewerApplication?.page || 1
        //     })
        // });
    };

    // Public API
    return {
        init,
        debug, // Expose debug function
        forceShowTooltip, // Expose force show function

        // Additional helper methods for external access
        showTooltip,
        hideTooltip,
        showNotification
    };
})();
