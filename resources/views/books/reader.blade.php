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
                        <div class="ml-4 flex space-x-2">
                            <button id="prev-page"
                                class="px-3 py-1 bg-gray-800 text-white rounded hover:bg-gray-700">Previous</button>
                            <button id="next-page"
                                class="px-3 py-1 bg-gray-800 text-white rounded hover:bg-gray-700">Next</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-0">
                    <!-- PDF Debug Info - Remove in production -->
                    <div class="p-4 bg-gray-100 text-sm">
                        <p>Reading: <strong>{{ $book->title }}</strong> by {{ $book->author }}</p>
                    </div>

                    <div id="pdf-container" class="w-full" style="height: calc(100vh - 260px); overflow: auto;">
                        <div id="pdf-loading" class="flex justify-center items-center h-full">
                            <div class="text-center">
                                <div
                                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-3">
                                </div>
                                <p>Loading PDF...</p>
                            </div>
                        </div>
                        <canvas id="pdf-viewer" class="mx-auto"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- Load PDF.js from CDN -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Set up PDF.js worker
                window.pdfjsLib.GlobalWorkerOptions.workerSrc =
                    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

                // Debug PDF URL
                const pdfUrl = "{{ $pdfUrl }}";
                console.log('PDF URL:', pdfUrl);

                // Show PDF URL in the debug area for troubleshooting
                const debugArea = document.querySelector('.p-4.bg-gray-100');
                debugArea.innerHTML +=
                    `<p class="mt-2 text-xs">PDF URL: <a href="${pdfUrl}" target="_blank" class="text-blue-600 underline">${pdfUrl}</a></p>`;

                const initialPage = {{ $enrollment ? $enrollment->current_page : 1 }};
                let pdfDoc = null;
                let pageNum = initialPage;
                let pageRendering = false;
                let pageNumPending = null;
                let canvas = document.getElementById('pdf-viewer');
                let ctx = canvas.getContext('2d');
                let pdfContainer = document.getElementById('pdf-container');
                let loadingIndicator = document.getElementById('pdf-loading');
                let scale = 1.5;

                // Load the PDF using PDF.js
                loadPDF();

                function loadPDF() {
                    // Try to load the PDF with proper error handling
                    debugArea.innerHTML += `<p class="mt-2 text-xs">Attempting to load PDF...</p>`;

                    // Create a fetch request to test if the file is accessible
                    fetch(pdfUrl, {
                            method: 'HEAD'
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            debugArea.innerHTML +=
                                `<p class="mt-2 text-xs text-green-600">PDF is accessible, loading now...</p>`;

                            // PDF is accessible, try to load it with PDF.js
                            return pdfjsLib.getDocument({
                                url: pdfUrl,
                                cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/cmaps/',
                                cMapPacked: true,
                            }).promise;
                        })
                        .then(pdf => {
                            console.log('PDF loaded successfully');
                            debugArea.innerHTML +=
                                `<p class="mt-2 text-xs text-green-600">PDF loaded! Pages: ${pdf.numPages}</p>`;

                            pdfDoc = pdf;

                            // Update total pages
                            const totalPages = pdf.numPages;
                            document.getElementById('total-pages').textContent = totalPages;

                            // Hide loading indicator
                            loadingIndicator.style.display = 'none';

                            // Update progress and render first page
                            updateProgress(pageNum, totalPages);
                            renderPage(pageNum);
                        })
                        .catch(error => {
                            console.error('Error loading PDF:', error);
                            debugArea.innerHTML +=
                                `<p class="mt-2 text-xs text-red-600">Error: ${error.message}</p>`;
                            loadingIndicator.innerHTML = `
                                <div class="text-center">
                                    <div class="text-red-500 mb-3">Error loading PDF: ${error.message}</div>
                                    <button id="retry-load" class="px-4 py-2 bg-blue-600 text-white rounded">Retry</button>
                                </div>`;

                            // Add retry button functionality
                            document.getElementById('retry-load').addEventListener('click', loadPDF);
                        });
                }

                // Render a specific page
                function renderPage(num) {
                    pageRendering = true;

                    // Update current page display
                    document.getElementById('current-page').textContent = num;

                    // Get the page from the PDF document
                    pdfDoc.getPage(num).then(function(page) {
                        // Calculate the scale to fit the page within the container width
                        const containerWidth = pdfContainer.clientWidth;
                        const viewport = page.getViewport({
                            scale: 1
                        });
                        const scaleFactor = containerWidth / viewport.width;
                        const scaledViewport = page.getViewport({
                            scale: Math.min(scale, scaleFactor * 0.95)
                        });

                        // Set canvas dimensions to match the viewport
                        canvas.height = scaledViewport.height;
                        canvas.width = scaledViewport.width;

                        // Render the PDF page
                        const renderContext = {
                            canvasContext: ctx,
                            viewport: scaledViewport
                        };

                        const renderTask = page.render(renderContext);

                        // When rendering is complete, update status
                        renderTask.promise.then(function() {
                            pageRendering = false;

                            // If another page is pending, render it
                            if (pageNumPending !== null) {
                                renderPage(pageNumPending);
                                pageNumPending = null;
                            }
                        });
                    });
                }

                // Queue the rendering of a page
                function queueRenderPage(num) {
                    if (pageRendering) {
                        pageNumPending = num;
                    } else {
                        renderPage(num);
                    }
                }

                // Go to previous page
                function onPrevPage() {
                    if (pageNum <= 1) {
                        return;
                    }
                    pageNum--;
                    queueRenderPage(pageNum);
                    updateProgress(pageNum, pdfDoc.numPages);
                }

                // Go to next page
                function onNextPage() {
                    if (pageNum >= pdfDoc.numPages) {
                        return;
                    }
                    pageNum++;
                    queueRenderPage(pageNum);
                    updateProgress(pageNum, pdfDoc.numPages);
                }

                // Update reading progress in the backend
                function updateProgress(currentPage, totalPages) {
                    if (!totalPages || totalPages < 1) return;

                    // Calculate and update the visual progress bar
                    const progressPercentage = Math.round((currentPage / totalPages) * 100);
                    document.getElementById('progress-percentage').textContent = progressPercentage;
                    document.getElementById('progress-bar').style.width = progressPercentage + '%';

                    // Save progress to the server
                    const data = {
                        current_page: currentPage,
                        total_pages: totalPages
                    };

                    fetch("{{ route('books.update-progress', $book) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Progress updated:', data);
                        })
                        .catch(error => {
                            console.error('Error updating progress:', error);
                        });
                }

                // Set up event listeners
                document.getElementById('prev-page').addEventListener('click', onPrevPage);
                document.getElementById('next-page').addEventListener('click', onNextPage);

                // Handle keyboard navigation
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowLeft') {
                        onPrevPage();
                    } else if (e.key === 'ArrowRight') {
                        onNextPage();
                    }
                });

                // Handle window resize
                window.addEventListener('resize', function() {
                    if (pdfDoc) {
                        // Re-render the current page when the window is resized
                        renderPage(pageNum);
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
