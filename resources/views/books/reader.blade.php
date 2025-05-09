<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reading: {{ $book->title }}
            </h2>
            <a href="{{ route('books.show', $book) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Back to Book
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex-1">
                            @if ($enrollment && $enrollment->total_pages)
                                <div class="flex flex-col">
                                    <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                                        <span>Page <span id="current-page">{{ $enrollment->current_page }}</span> of
                                            <span id="total-pages">{{ $enrollment->total_pages }}</span></span>
                                        <span><span
                                                id="progress-percentage">{{ $enrollment->getProgressPercentage() }}</span>%
                                            completed</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full" id="progress-bar"
                                            style="width: {{ $enrollment->getProgressPercentage() }}%"></div>
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-col">
                                    <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                                        <span>Page <span id="current-page">1</span> of <span
                                                id="total-pages">-</span></span>
                                        <span><span id="progress-percentage">0</span>% completed</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full" id="progress-bar" style="width: 0%">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-0">
                    <div id="pdfjs-container" style="height: calc(100vh - 250px);">
                        @php
                            $initialPage = $enrollment ? $enrollment->current_page : 1;
                        @endphp
                        <iframe id="pdf-viewer"
                            src="{{ asset('pdfjs/web/viewer.html') }}?file={{ urlencode($pdfUrl) }}#page={{ $initialPage }}"
                            width="100%" height="100%" frameborder="0"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const initialPage = {{ $enrollment ? $enrollment->current_page : 1 }};
                const pdfViewer = document.getElementById('pdf-viewer');
                let viewerLoaded = false;

                // Set up message listener to communicate with the PDF.js viewer
                window.addEventListener('message', function(e) {
                    // Security check - only accept messages from our PDF.js viewer iframe
                    if (e.source !== pdfViewer.contentWindow) return;

                    const message = e.data;

                    // Handle page change events from PDF.js
                    if (message && message.type === 'pagechange') {
                        const currentPage = message.page;
                        const totalPages = message.total || document.getElementById('total-pages').textContent;

                        // Update UI
                        document.getElementById('current-page').textContent = currentPage;
                        document.getElementById('total-pages').textContent = totalPages;
                        updateProgress(currentPage, totalPages);

                        // Save progress
                        saveProgressDebounced(currentPage, totalPages);
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

                            // Inject communication script into PDF.js iframe
                            const frameDoc = frameWindow.document;
                            const script = frameDoc.createElement('script');
                            script.textContent = `
                                (function() {
                                    const PDFViewerApplication = window.PDFViewerApplication;

                                    // Add page change event listener
                                    PDFViewerApplication.eventBus.on('pagechanging', function(evt) {
                                        // Send message to parent window
                                        window.parent.postMessage({
                                            type: 'pagechange',
                                            page: evt.pageNumber,
                                            total: PDFViewerApplication.pagesCount
                                        }, '*');
                                    });

                                    // Set initial page after document is fully loaded
                                    if (!PDFViewerApplication.pdfDocument) {
                                        PDFViewerApplication.eventBus.on('documentloaded', function() {
                                            // Force the page change event to trigger
                                            window.parent.postMessage({
                                                type: 'pagechange',
                                                page: PDFViewerApplication.page,
                                                total: PDFViewerApplication.pagesCount
                                            }, '*');
                                        });
                                    } else {
                                        // Document already loaded, force event
                                        window.parent.postMessage({
                                            type: 'pagechange',
                                            page: PDFViewerApplication.page,
                                            total: PDFViewerApplication.pagesCount
                                        }, '*');
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
                    return function(currentPage, totalPages) {
                        clearTimeout(timer);
                        timer = setTimeout(() => {
                            saveProgress(currentPage, totalPages);
                        }, 500);
                    };
                })();

                // Update the progress UI
                function updateProgress(currentPage, totalPages) {
                    if (!totalPages || isNaN(totalPages)) return;

                    const progressPercentage = Math.round((currentPage / totalPages) * 100);
                    document.getElementById('progress-percentage').textContent = progressPercentage;
                    document.getElementById('progress-bar').style.width = progressPercentage + '%';
                }

                // Save progress to the server
                function saveProgress(currentPage, totalPages, isSynchronous = false) {
                    const requestData = {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            current_page: currentPage,
                            total_pages: totalPages,
                            last_read_at: new Date().toISOString()
                        })
                    };

                    // If synchronous (like beforeunload), use navigator.sendBeacon
                    if (isSynchronous && navigator.sendBeacon) {
                        const blob = new Blob([JSON.stringify({
                            current_page: currentPage,
                            total_pages: totalPages,
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
                        saveProgress(currentPage, totalPages, true);
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
