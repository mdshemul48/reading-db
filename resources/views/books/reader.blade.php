<x-app-layout>
    <!-- Remove the default header to maximize space -->
    <x-slot name="header"></x-slot>

    <!-- Custom minimal floating header -->
    <div class="reader-header fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="reader-header">
        <div class="bg-white shadow-md mx-auto px-4 py-2 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <a href="{{ route('books.show', $book) }}"
                    class="text-gray-600 hover:text-gray-900 p-2 rounded-full hover:bg-gray-100" title="Back to Book">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h2 class="font-medium text-gray-800 truncate max-w-xs md:max-w-md">
                    {{ $book->title }}
                </h2>
            </div>

            <div class="flex items-center space-x-1">
                @if ($enrollment && $enrollment->total_pages)
                    <div class="hidden md:flex items-center text-sm text-gray-600 mr-2">
                        <span>Page <span id="current-page">{{ $enrollment->current_page }}</span> of
                            <span id="total-pages">{{ $enrollment->total_pages }}</span>
                            (<span id="progress-percentage">{{ $enrollment->getProgressPercentage() }}</span>%)</span>
                    </div>
                @endif

                <button id="toggle-header" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full"
                    title="Toggle Header">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                </button>

                <button id="fullscreen-toggle"
                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full"
                    title="Toggle Fullscreen">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Progress bar -->
        @if ($enrollment && $enrollment->total_pages)
            <div class="w-full bg-gray-200 h-1">
                <div class="bg-blue-600 h-1" id="progress-bar"
                    style="width: {{ $enrollment->getProgressPercentage() }}%"></div>
            </div>
        @endif
    </div>

    <!-- Main PDF container - takes up full viewport height -->
    <div class="pdf-container-wrapper" style="height: 100vh; padding-top: 0">
        <div id="pdfjs-container" class="w-full h-full">
            @php
                $initialPage = $enrollment ? $enrollment->current_page : 1;
                $initialScrollPosition =
                    $enrollment && $enrollment->scroll_position !== null ? $enrollment->scroll_position : null;
            @endphp
            <iframe id="pdf-viewer"
                src="{{ asset('pdfjs/web/viewer.html') }}?file={{ urlencode($pdfUrl) }}#page={{ $initialPage }}"
                width="100%" height="100%" frameborder="0"></iframe>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const initialPage = {{ $enrollment ? $enrollment->current_page : 1 }};
                const initialScrollPosition =
                    {{ $enrollment && $enrollment->scroll_position !== null ? $enrollment->scroll_position : 'null' }};
                const pdfViewer = document.getElementById('pdf-viewer');
                let viewerLoaded = false;

                // Header toggle functionality
                const readerHeader = document.getElementById('reader-header');
                const toggleHeaderBtn = document.getElementById('toggle-header');
                let headerVisible = true;

                toggleHeaderBtn.addEventListener('click', function() {
                    if (headerVisible) {
                        readerHeader.style.transform = 'translateY(-100%)';
                        toggleHeaderBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        `;
                        toggleHeaderBtn.style.position = 'fixed';
                        toggleHeaderBtn.style.top = '0.5rem';
                        toggleHeaderBtn.style.right = '0.5rem';
                        toggleHeaderBtn.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
                        toggleHeaderBtn.style.zIndex = '60';
                    } else {
                        readerHeader.style.transform = 'translateY(0)';
                        toggleHeaderBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                        `;
                        toggleHeaderBtn.style.position = 'static';
                        toggleHeaderBtn.style.backgroundColor = 'transparent';
                    }
                    headerVisible = !headerVisible;
                });

                // Fullscreen functionality
                const fullscreenToggle = document.getElementById('fullscreen-toggle');
                const pdfContainerWrapper = document.querySelector('.pdf-container-wrapper');

                fullscreenToggle.addEventListener('click', function() {
                    if (!document.fullscreenElement) {
                        if (pdfContainerWrapper.requestFullscreen) {
                            pdfContainerWrapper.requestFullscreen();
                        } else if (pdfContainerWrapper.webkitRequestFullscreen) {
                            pdfContainerWrapper.webkitRequestFullscreen();
                        } else if (pdfContainerWrapper.msRequestFullscreen) {
                            pdfContainerWrapper.msRequestFullscreen();
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        } else if (document.msExitFullscreen) {
                            document.msExitFullscreen();
                        }
                    }
                });

                // Auto-hide header after 3 seconds of inactivity
                let headerTimeout;

                function resetHeaderTimeout() {
                    clearTimeout(headerTimeout);
                    if (!headerVisible) {
                        readerHeader.style.transform = 'translateY(0)';
                        headerVisible = true;
                        toggleHeaderBtn.style.position = 'static';
                        toggleHeaderBtn.style.backgroundColor = 'transparent';
                        toggleHeaderBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                        `;
                    }

                    headerTimeout = setTimeout(() => {
                        if (headerVisible) {
                            readerHeader.style.transform = 'translateY(-100%)';
                            headerVisible = false;
                            toggleHeaderBtn.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            `;
                            toggleHeaderBtn.style.position = 'fixed';
                            toggleHeaderBtn.style.top = '0.5rem';
                            toggleHeaderBtn.style.right = '0.5rem';
                            toggleHeaderBtn.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
                            toggleHeaderBtn.style.zIndex = '60';
                        }
                    }, 3000);
                }

                document.addEventListener('mousemove', resetHeaderTimeout);
                resetHeaderTimeout();

                // Show header when hovering near top of screen
                document.addEventListener('mousemove', function(e) {
                    if (e.clientY < 20 && !headerVisible) {
                        readerHeader.style.transform = 'translateY(0)';
                        headerVisible = true;
                        resetHeaderTimeout();
                    }
                });

                // Set up message listener to communicate with the PDF.js viewer
                window.addEventListener('message', function(e) {
                    // Security check - only accept messages from our PDF.js viewer iframe
                    if (e.source !== pdfViewer.contentWindow) return;

                    const message = e.data;

                    // Handle page change events from PDF.js
                    if (message && message.type === 'pagechange') {
                        const currentPage = message.page;
                        const totalPages = message.total || document.getElementById('total-pages').textContent;
                        const scrollPosition = message.scrollPosition || null;

                        // Update UI
                        document.getElementById('current-page').textContent = currentPage;
                        document.getElementById('total-pages').textContent = totalPages;
                        updateProgress(currentPage, totalPages, scrollPosition);

                        // Save progress
                        saveProgressDebounced(currentPage, totalPages, scrollPosition);
                    }
                });

                // Hook into PDF.js iframe load
                pdfViewer.addEventListener('load', function() {
                    console.log('PDF.js viewer loaded');

                    // We'll use multiple approaches to ensure the initial page is set correctly
                    setInitialPage();
                });

                function setInitialPage() {
                    // Retry mechanism to ensure PDF.js is properly loaded and page is set
                    let attempts = 0;
                    const maxAttempts = 10;

                    function attemptSetPage() {
                        if (attempts >= maxAttempts) {
                            console.warn("Maximum attempts reached for setting initial page");
                            return;
                        }

                        attempts++;

                        try {
                            const frameWindow = pdfViewer.contentWindow;

                            // Check if PDF.js is loaded and initialized
                            if (!frameWindow.PDFViewerApplication ||
                                !frameWindow.PDFViewerApplication.initialized ||
                                !frameWindow.PDFViewerApplication.pdfViewer) {
                                console.log(`PDF.js not yet ready (attempt ${attempts}), retrying in 500ms...`);
                                setTimeout(attemptSetPage, 500);
                                return;
                            }

                            const PDFViewerApplication = frameWindow.PDFViewerApplication;

                            // Apply custom styling to PDF.js viewer to optimize reading experience
                            try {
                                const frameDoc = frameWindow.document;
                                const styleEl = frameDoc.createElement('style');
                                styleEl.textContent = `
                                    #toolbarContainer {
                                        box-shadow: none !important;
                                    }
                                    #viewerContainer {
                                        background-color: #f7f7f7 !important;
                                    }
                                    .page {
                                        box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
                                        margin-bottom: 16px !important;
                                    }
                                `;
                                frameDoc.head.appendChild(styleEl);
                            } catch (err) {
                                console.warn('Could not apply custom styling', err);
                            }

                            // Inject communication script into PDF.js iframe
                            const frameDoc = frameWindow.document;
                            const script = frameDoc.createElement('script');
                            script.textContent = `
                                (function() {
                                    const PDFViewerApplication = window.PDFViewerApplication;
                                    const initialScrollPosition = ${initialScrollPosition};
                                    let isFirstPageLoad = true;

                                    // Function to get current scroll position as percentage (0-1)
                                    function getCurrentScrollPosition() {
                                        const container = PDFViewerApplication.pdfViewer.container;
                                        if (!container) return 0;

                                        const visibleHeight = container.clientHeight;
                                        const pageView = PDFViewerApplication.pdfViewer._pages[PDFViewerApplication.page - 1];
                                        if (!pageView) return 0;

                                        const pageViewport = pageView.viewport;
                                        const pageHeight = pageViewport.height;
                                        const pageTop = pageView.div.offsetTop - container.offsetTop;
                                        const scrollTop = container.scrollTop;

                                        // Calculate position in the current page (0-1)
                                        let position = (scrollTop - pageTop) / pageHeight;
                                        position = Math.max(0, Math.min(1, position));

                                        return position;
                                    }

                                    // Add page change event listener
                                    PDFViewerApplication.eventBus.on('pagechanging', function(evt) {
                                        // Send message to parent window
                                        window.parent.postMessage({
                                            type: 'pagechange',
                                            page: evt.pageNumber,
                                            total: PDFViewerApplication.pagesCount,
                                            scrollPosition: 0 // Reset to top when changing pages
                                        }, '*');
                                    });

                                    // Add scroll event listener to track scroll position
                                    let scrollTimeout;
                                    PDFViewerApplication.pdfViewer.container.addEventListener('scroll', function() {
                                        clearTimeout(scrollTimeout);
                                        scrollTimeout = setTimeout(() => {
                                            const scrollPosition = getCurrentScrollPosition();

                                            // Send message to parent window
                                            window.parent.postMessage({
                                                type: 'pagechange',
                                                page: PDFViewerApplication.page,
                                                total: PDFViewerApplication.pagesCount,
                                                scrollPosition: scrollPosition
                                            }, '*');
                                        }, 200);
                                    });

                                    // Set initial page and scroll position after document is fully loaded
                                    if (!PDFViewerApplication.pdfDocument) {
                                        PDFViewerApplication.eventBus.on('documentloaded', function() {
                                            // Force the page change event to trigger
                                            window.parent.postMessage({
                                                type: 'pagechange',
                                                page: PDFViewerApplication.page,
                                                total: PDFViewerApplication.pagesCount,
                                                scrollPosition: 0
                                            }, '*');

                                            // Set initial scroll position after page is rendered
                                            if (initialScrollPosition !== null) {
                                                setTimeout(() => {
                                                    if (PDFViewerApplication.page === ${initialPage}) {
                                                        const pageView = PDFViewerApplication.pdfViewer._pages[PDFViewerApplication.page - 1];
                                                        if (pageView) {
                                                            const pageHeight = pageView.viewport.height;
                                                            const pageTop = pageView.div.offsetTop - PDFViewerApplication.pdfViewer.container.offsetTop;
                                                            const scrollTo = pageTop + (pageHeight * initialScrollPosition);
                                                            PDFViewerApplication.pdfViewer.container.scrollTop = scrollTo;
                                                        }
                                                    }
                                                }, 500);
                                            }
                                        });

                                        // Add handler for when pages are rendered
                                        PDFViewerApplication.eventBus.on('pagesloaded', function() {
                                            if (isFirstPageLoad && initialScrollPosition !== null) {
                                                isFirstPageLoad = false;

                                                setTimeout(() => {
                                                    if (PDFViewerApplication.page === ${initialPage}) {
                                                        const pageView = PDFViewerApplication.pdfViewer._pages[PDFViewerApplication.page - 1];
                                                        if (pageView) {
                                                            const pageHeight = pageView.viewport.height;
                                                            const pageTop = pageView.div.offsetTop - PDFViewerApplication.pdfViewer.container.offsetTop;
                                                            const scrollTo = pageTop + (pageHeight * initialScrollPosition);
                                                            PDFViewerApplication.pdfViewer.container.scrollTop = scrollTo;
                                                        }
                                                    }
                                                }, 500);
                                            }
                                        });
                                    } else {
                                        // Document already loaded, force event
                                        window.parent.postMessage({
                                            type: 'pagechange',
                                            page: PDFViewerApplication.page,
                                            total: PDFViewerApplication.pagesCount,
                                            scrollPosition: getCurrentScrollPosition()
                                        }, '*');

                                        // Set initial scroll position
                                        if (initialScrollPosition !== null && PDFViewerApplication.page === ${initialPage}) {
                                            setTimeout(() => {
                                                const pageView = PDFViewerApplication.pdfViewer._pages[PDFViewerApplication.page - 1];
                                                if (pageView) {
                                                    const pageHeight = pageView.viewport.height;
                                                    const pageTop = pageView.div.offsetTop - PDFViewerApplication.pdfViewer.container.offsetTop;
                                                    const scrollTo = pageTop + (pageHeight * initialScrollPosition);
                                                    PDFViewerApplication.pdfViewer.container.scrollTop = scrollTo;
                                                }
                                            }, 500);
                                        }
                                    }
                                })();
                            `;

                            // Append script to PDF.js document body
                            frameDoc.body.appendChild(script);

                            console.log('Successfully injected PDF.js script');
                        } catch (err) {
                            console.error('Error setting initial page:', err);
                            setTimeout(attemptSetPage, 500);
                        }
                    }

                    // Start the attempt process
                    setTimeout(attemptSetPage, 500);
                }

                // Debounce function for saving progress
                const saveProgressDebounced = (function() {
                    let timer;
                    return function(currentPage, totalPages, scrollPosition) {
                        clearTimeout(timer);
                        timer = setTimeout(() => {
                            saveProgress(currentPage, totalPages, scrollPosition);
                        }, 500);
                    };
                })();

                // Update the progress UI
                function updateProgress(currentPage, totalPages, scrollPosition) {
                    if (!totalPages || isNaN(totalPages)) return;

                    const progressPercentage = Math.round((currentPage / totalPages) * 100);
                    document.getElementById('progress-percentage').textContent = progressPercentage;
                    document.getElementById('progress-bar').style.width = progressPercentage + '%';
                }

                // Save progress to the server
                function saveProgress(currentPage, totalPages, scrollPosition, isSynchronous = false) {
                    const requestData = {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            current_page: currentPage,
                            total_pages: totalPages,
                            scroll_position: scrollPosition,
                            last_read_at: new Date().toISOString()
                        })
                    };

                    // If synchronous (like beforeunload), use navigator.sendBeacon
                    if (isSynchronous && navigator.sendBeacon) {
                        const blob = new Blob([JSON.stringify({
                            current_page: currentPage,
                            total_pages: totalPages,
                            scroll_position: scrollPosition,
                            last_read_at: new Date().toISOString()
                        })], {
                            type: 'application/json'
                        });

                        navigator.sendBeacon("{{ route('books.update-progress', $book) }}", blob);
                        return;
                    }

                    // Otherwise use standard fetch
                    fetch("{{ route('books.update-progress', $book) }}", requestData)
                        .then(response => response.json())
                        .then(data => {
                            console.log('Progress updated:', data);
                        })
                        .catch(error => {
                            console.error('Error updating progress:', error);
                        });
                }

                // Save progress when user leaves the page
                window.addEventListener('beforeunload', function() {
                    const currentPage = parseInt(document.getElementById('current-page').textContent);
                    const totalPages = parseInt(document.getElementById('total-pages').textContent);

                    if (!isNaN(currentPage) && !isNaN(totalPages)) {
                        // We'll send null for scroll position here because we don't have it
                        // but that's ok because the latest scroll position would have been saved before
                        saveProgress(currentPage, totalPages, null, true);
                    }
                });
            });
        </script>
    @endpush

    <style>
        /* Fix body padding for reader page */
        body {
            padding-top: 0 !important;
        }

        /* Style the reader header */
        .reader-header {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
        }

        /* Style the toggle button */
        #toggle-header,
        #fullscreen-toggle {
            transition: all 0.3s ease;
        }

        /* Remove padding to maximize PDF viewer space */
        .py-12 {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        /* Make sure PDF container takes full viewport */
        .pdf-container-wrapper {
            margin: 0 !important;
            max-width: 100% !important;
            padding: 0 !important;
        }
    </style>
</x-app-layout>
