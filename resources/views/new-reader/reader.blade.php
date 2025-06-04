<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PDF Reader') }} - {{ isset($book) ? $book->title : 'Document' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js"></script>

    <!-- Reader Styles -->
    <link href="{{ asset('css/pdf-reader.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/pdf-tooltip.css') }}" rel="stylesheet" />

    <!-- PDF Reader Module Scripts -->
    <script src="{{ asset('js/pdf-modules/core.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/loader.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/renderer.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/scroll.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/progress.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/navigation.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/color-manager.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/ui-events.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/highlights.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/tooltip.js') }}"></script>

    <!-- Create a tooltip element early to avoid timing issues -->
    <script>
        // Initialize the tooltip element immediately
        document.addEventListener('DOMContentLoaded', function() {
            // Create tooltip element if it doesn't exist yet
            if (!document.getElementById('pdf-text-tooltip')) {
                const tooltip = document.createElement('div');
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
                console.log('Tooltip element created early to avoid timing issues');

                // Setup auto-hide behavior for the early tooltip
                if (typeof setupTooltipAutoHide === 'function') {
                    setupTooltipAutoHide(tooltip);
                } else {
                    // If setupTooltipAutoHide isn't defined yet, wait and try again
                    window.addEventListener('load', function() {
                        if (typeof setupTooltipAutoHide === 'function') {
                            setupTooltipAutoHide(tooltip);
                        }
                    });
                }
            }
        });

        // Direct tooltip implementation that doesn't rely on TooltipManager
        window.showPdfTooltip = function(text, x, y) {
            console.log('showPdfTooltip called with:', text, x, y);

            // Get or create tooltip
            let tooltip = document.getElementById('pdf-text-tooltip');
            if (!tooltip) {
                console.log('Creating tooltip element on-demand');
                tooltip = document.createElement('div');
                tooltip.id = 'pdf-text-tooltip';
                tooltip.className = 'pdf-text-tooltip';
                tooltip.innerHTML = `
                    <button class="tooltip-btn highlight-btn" data-action="highlight">Highlight</button>
                    <button class="tooltip-btn copy-btn" data-action="copy">Copy</button>
                    <button class="tooltip-btn note-btn" data-action="note">Add Note</button>
                    <button class="tooltip-btn search-btn" data-action="search">Search Web</button>
                `;
                document.body.appendChild(tooltip);

                // Add event listeners to buttons
                tooltip.querySelectorAll('.tooltip-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        const action = e.currentTarget.getAttribute('data-action');
                        console.log('Tooltip action clicked:', action);

                        switch (action) {
                            case 'copy':
                                navigator.clipboard.writeText(text)
                                    .then(() => {
                                        showPdfNotification('Text copied to clipboard');
                                    })
                                    .catch(err => {
                                        console.error('Failed to copy text:', err);
                                    });
                                break;
                            case 'search':
                                window.open(
                                    `https://www.google.com/search?q=${encodeURIComponent(text)}`,
                                    '_blank');
                                break;
                        }

                        // Hide tooltip
                        tooltip.style.display = 'none';
                    });
                });

                // Setup auto-hide behavior for the newly created tooltip
                setupTooltipAutoHide(tooltip);
            }

            // Store selected text as a data attribute
            tooltip.setAttribute('data-selected-text', text);

            // Position the tooltip
            tooltip.style.left = `${x}px`;
            tooltip.style.top = `${y}px`;

            // Show the tooltip
            tooltip.style.display = 'flex';
            console.log('Tooltip displayed at', x, y);

            // Try to use TooltipManager if available (but we don't depend on it)
            if (window.TooltipManager) {
                console.log('TooltipManager found, using it as backup');
                try {
                    if (typeof window.TooltipManager.forceShowTooltip === 'function') {
                        window.TooltipManager.forceShowTooltip(text, x, y);
                    } else if (window.TooltipManager.showTooltip) {
                        window.selectedText = text;
                        window.TooltipManager.showTooltip();
                    }
                } catch (err) {
                    console.error('Error using TooltipManager:', err);
                    // We already displayed the tooltip, so this is just a backup
                }
            }
        };

        // Hide tooltip when mouse moves away
        function setupTooltipAutoHide(tooltipElement) {
            const tooltip = tooltipElement || document.getElementById('pdf-text-tooltip');
            if (!tooltip) return;

            // If the tooltip already has event listeners set up, don't add them again
            if (tooltip.hasAttribute('data-autohide-setup')) return;
            tooltip.setAttribute('data-autohide-setup', 'true');

            let isOverTooltip = false;

            // Track when mouse is over the tooltip
            tooltip.addEventListener('mouseenter', function() {
                isOverTooltip = true;
                console.log('Mouse entered tooltip');
            });

            tooltip.addEventListener('mouseleave', function() {
                isOverTooltip = false;
                console.log('Mouse left tooltip');
            });

            // Hide tooltip when mouse moves away
            const mouseMoveHandler = function(e) {
                if (!tooltip || tooltip.style.display === 'none') return;

                // Don't hide if mouse is over the tooltip
                if (isOverTooltip) return;

                // Get tooltip position and dimensions
                const tooltipRect = tooltip.getBoundingClientRect();

                // Define an expanded area around the tooltip (buffer zone)
                const buffer = 50; // pixels
                const expandedArea = {
                    left: tooltipRect.left - buffer,
                    right: tooltipRect.right + buffer,
                    top: tooltipRect.top - buffer,
                    bottom: tooltipRect.bottom + buffer
                };

                // Check if mouse is outside the expanded area
                if (e.clientX < expandedArea.left ||
                    e.clientX > expandedArea.right ||
                    e.clientY < expandedArea.top ||
                    e.clientY > expandedArea.bottom) {

                    // Check if we still have a text selection
                    const selection = window.getSelection();
                    const selectedText = selection.toString().trim();

                    // Only hide if text selection is gone or different
                    if (!selectedText || selectedText !== tooltip.getAttribute('data-selected-text')) {
                        tooltip.style.display = 'none';
                        console.log('Hiding tooltip - mouse moved away');
                    }
                }
            };

            // Add the event listener only once
            if (!window.tooltipMouseMoveHandlerAdded) {
                document.addEventListener('mousemove', mouseMoveHandler);
                window.tooltipMouseMoveHandlerAdded = true;
                console.log('Added global mousemove handler for tooltip auto-hide');
            }
        }

        // Show notification
        function showPdfNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'pdf-notification';
            notification.textContent = message;
            document.body.appendChild(notification);

            // Remove after 2 seconds
            setTimeout(() => {
                notification.classList.add('fade-out');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 1700);
        }
    </script>
</head>

<body>
    <div class="reader-container">
        <!-- Reader Header -->
        <div class="reader-header" id="reader-header">
            <div class="header-left">
                <a href="{{ isset($book) ? route('books.show', $book) : '/' }}" class="btn" title="Back">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h2 class="title">{{ isset($book) ? $book->title : 'Document' }}</h2>
            </div>

            <div class="header-center">
                <span id="page-info">Page <span id="current-page">0</span> of <span id="total-pages">0</span></span>
            </div>

            <div class="header-right">
                <div class="color-settings-toggle btn" id="color-settings-toggle" title="Color & Display Settings">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>

                <button id="zoom-in" class="btn" title="Zoom In">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                    </svg>
                </button>

                <button id="zoom-out" class="btn" title="Zoom Out">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                    </svg>
                </button>

                <button id="fullscreen-toggle" class="btn" title="Toggle Fullscreen">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-bar-container">
            <div class="progress-bar" id="progress-bar" style="width: 0%"></div>
        </div>

        <!-- PDF Container -->
        <div id="pdf-container">
            <div id="pdf-viewer"></div>
        </div>

        <!-- Color Settings Panel -->
        <div class="color-settings-panel" id="color-settings-panel">
            <h3>
                Color & Display Settings
                <button class="close-btn" id="close-settings-btn">&times;</button>
            </h3>

            <div class="form-group">
                <label for="color-preset-selector">Color Theme</label>
                <select id="color-preset-selector">
                    <option value="light">Light</option>
                    <option value="sepia">Sepia</option>
                    <option value="green">Green</option>
                    <option value="dark">Dark</option>
                    <option value="warmDark">Warm Dark</option>
                    <option value="darkBlue">Dark Blue</option>
                    <option value="darkGreen">Dark Green</option>
                </select>
            </div>

            <div class="form-group">
                <label for="brightness-slider">Brightness</label>
                <div class="range-container">
                    <input type="range" id="brightness-slider" min="50" max="150" step="5"
                        value="100">
                    <span class="range-value" id="brightness-value">100%</span>
                </div>
            </div>

            <div class="form-group">
                <label for="contrast-slider">Contrast</label>
                <div class="range-container">
                    <input type="range" id="contrast-slider" min="50" max="150" step="5"
                        value="100">
                    <span class="range-value" id="contrast-value">100%</span>
                </div>
            </div>

            <div class="form-group">
                <label for="warmth-slider">Warmth</label>
                <div class="range-container">
                    <input type="range" id="warmth-slider" min="0" max="100" step="5"
                        value="0">
                    <span class="range-value" id="warmth-value">0%</span>
                </div>
            </div>

            <div class="buttons">
                <button class="reset" id="reset-settings-btn">Reset</button>
                <button class="apply" id="apply-settings-btn">Apply</button>
            </div>
        </div>
    </div>

    <!-- PDF Reader Main Script -->
    <script src="{{ asset('js/pdf-reader.js') }}"></script>
    <script>
        // Configuration for the PDF reader
        window.pdfUrl = "{{ isset($pdfUrl) ? $pdfUrl : asset('sample.pdf') }}";

        @if (isset($book) && isset($enrollment))
            window.saveProgressUrl = "{{ route('books.update-progress', $book) }}";
            window.initialPage = {{ isset($enrollment) && $enrollment->current_page ? $enrollment->current_page : 1 }};
        @endif

        // Initialize modules after the page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Initializing PDF reader');

            // Setup tooltip auto-hide behavior
            setupTooltipAutoHide();

            // Add event listener for when PDF is fully loaded and rendered
            const pdfContainer = document.getElementById('pdf-container');

            // Ensure tooltip is initialized early
            if (window.TooltipManager) {
                console.log('TooltipManager found, initializing');
                TooltipManager.init();
                console.log('TooltipManager initialized on page load');

                // Expose TooltipManager methods to window for direct access
                window.forceShowTooltip = function(text, x, y) {
                    TooltipManager.forceShowTooltip(text, x, y);
                };
            } else {
                console.log('TooltipManager not found yet, will try again later');

                // Wait for TooltipManager to load
                const checkInterval = setInterval(function() {
                    if (window.TooltipManager) {
                        console.log('TooltipManager found after waiting');
                        clearInterval(checkInterval);
                        TooltipManager.init();
                    }
                }, 500);
            }

            // Remove the test tooltip that appears on page load
            // Test the tooltip directly
            // setTimeout(function() {
            //     console.log('Testing direct tooltip display');
            //     showPdfTooltip('Test tooltip', window.innerWidth/2 - 100, 100);
            // }, 1000);

            // First initialize
            setTimeout(function() {
                console.log('Initializing tooltip and highlight managers');
                if (window.HighlightManager) {
                    HighlightManager.init();
                }

                // Direct implementation of text selection handler that bypasses all other handlers
                document.addEventListener('mouseup', function(e) {
                    console.log('Direct mouseup event on document', e.target);

                    setTimeout(function() {
                        const selection = window.getSelection();
                        const text = selection.toString().trim();

                        if (text) {
                            console.log('Selected text directly captured:', text);

                            try {
                                // Get position information
                                if (selection.rangeCount > 0) {
                                    const range = selection.getRangeAt(0);
                                    const rect = range.getBoundingClientRect();

                                    // Force show the tooltip directly
                                    showPdfTooltip(text, rect.left + (rect.width / 2) - 100,
                                        rect.top - 45);
                                    console.log('Forced tooltip to show at', rect.left, rect
                                        .top);
                                }
                            } catch (err) {
                                console.error('Error showing tooltip:', err);
                            }
                        }
                    }, 10);
                });

                // Add the same for touchend for mobile devices
                document.addEventListener('touchend', function(e) {
                    console.log('Direct touchend event on document', e.target);

                    setTimeout(function() {
                        const selection = window.getSelection();
                        const text = selection.toString().trim();

                        if (text) {
                            console.log('Selected text directly captured (touch):', text);

                            try {
                                // Get position information
                                if (selection.rangeCount > 0) {
                                    const range = selection.getRangeAt(0);
                                    const rect = range.getBoundingClientRect();

                                    // Force show the tooltip directly
                                    showPdfTooltip(text, rect.left + (rect.width / 2) - 100,
                                        rect.top - 45);
                                    console.log('Forced tooltip to show at', rect.left, rect
                                        .top);
                                }
                            } catch (err) {
                                console.error('Error showing tooltip:', err);
                            }
                        }
                    }, 10);
                });
            }, 1000);

            // Also initialize when new pages are added (for dynamic loading)
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                        // If new textLayer is added, reinitialize tooltip
                        for (let i = 0; i < mutation.addedNodes.length; i++) {
                            const node = mutation.addedNodes[i];
                            if (node.classList &&
                                (node.classList.contains('page') ||
                                    node.classList.contains('textLayer'))) {
                                console.log('New page or textLayer detected, initializing tooltip');
                                if (window.TooltipManager) {
                                    TooltipManager.init();
                                }

                                // Add a direct mouseup handler to the textLayer
                                if (node.classList.contains('textLayer')) {
                                    node.addEventListener('mouseup', function(e) {
                                        console.log('Direct textLayer mouseup', e.target);
                                        // Check if text is selected
                                        const selection = window.getSelection();
                                        const text = selection.toString().trim();
                                        if (text) {
                                            console.log(
                                                'Text selected from direct handler:',
                                                text);

                                            // Force show the tooltip
                                            try {
                                                // Get position information
                                                if (selection.rangeCount > 0) {
                                                    const range = selection.getRangeAt(0);
                                                    const rect = range
                                                        .getBoundingClientRect();

                                                    showPdfTooltip(text, rect.left + (rect
                                                            .width / 2) - 100, rect
                                                        .top - 45);
                                                    console.log('Forced tooltip to show at',
                                                        rect.left, rect.top);
                                                }
                                            } catch (err) {
                                                console.error('Error showing tooltip:',
                                                    err);
                                            }
                                        }
                                    });

                                    // Add handler to all spans in this text layer
                                    const spans = node.querySelectorAll('span');
                                    spans.forEach(span => {
                                        span.addEventListener('mouseup', function(e) {
                                            console.log('Direct span mouseup', this
                                                .textContent);
                                            // Direct span handling for selection
                                            setTimeout(() => {
                                                const selection = window
                                                    .getSelection();
                                                const text = selection
                                                    .toString().trim();
                                                if (text) {
                                                    console.log(
                                                        'Text selected from span:',
                                                        text);

                                                    try {
                                                        // Get position information
                                                        if (selection
                                                            .rangeCount > 0
                                                        ) {
                                                            const range =
                                                                selection
                                                                .getRangeAt(
                                                                    0);
                                                            const rect =
                                                                range
                                                                .getBoundingClientRect();

                                                            showPdfTooltip(
                                                                text,
                                                                rect
                                                                .left +
                                                                (rect
                                                                    .width /
                                                                    2) -
                                                                100,
                                                                rect
                                                                .top -
                                                                45);
                                                            console.log(
                                                                'Forced tooltip to show from span at',
                                                                rect
                                                                .left,
                                                                rect.top
                                                            );
                                                        }
                                                    } catch (err) {
                                                        console.error(
                                                            'Error showing tooltip from span:',
                                                            err);
                                                    }
                                                }
                                            }, 10);
                                        });
                                    });
                                }
                            }
                        }
                    }
                });
            });

            observer.observe(pdfContainer, {
                childList: true,
                subtree: true
            });
        });
    </script>
</body>

</html>
