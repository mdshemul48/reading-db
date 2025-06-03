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

            <button id="toggle-annotations" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full"
                title="Show Annotations">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </button>

            <button id="toggle-header" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full"
                title="Toggle Header">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>

            <button id="fullscreen-toggle" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full"
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
            <div class="bg-blue-600 h-1" id="progress-bar" style="width: {{ $enrollment->getProgressPercentage() }}%">
            </div>
        </div>
    @endif
</div>

<!-- Add JavaScript for the dark mode toggle button -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        if (!darkModeToggle) return;

        // Initialize dark mode state from localStorage or default to true (dark mode on)
        let isDarkMode = localStorage.getItem('pdf-dark-mode') === null ?
            true :
            localStorage.getItem('pdf-dark-mode') !== 'false';

        // Function to update the icon based on dark mode state
        const updateIcon = () => {
            if (isDarkMode) {
                darkModeToggle.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>`;
                darkModeToggle.title = "Switch to Light Mode";
            } else {
                darkModeToggle.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>`;
                darkModeToggle.title = "Switch to Dark Mode";
            }
        };

        // Update icon initially
        updateIcon();

        // Toggle dark mode when button is clicked
        darkModeToggle.addEventListener('click', function() {
            isDarkMode = !isDarkMode;
            localStorage.setItem('pdf-dark-mode', isDarkMode);
            updateIcon();

            // Apply dark mode immediately to the PDF iframe
            const pdfViewer = document.getElementById('pdf-viewer');
            if (pdfViewer) {
                try {
                    const iframeDoc = pdfViewer.contentDocument || pdfViewer.contentWindow.document;
                    if (iframeDoc) {
                        const darkModeLink = iframeDoc.getElementById('pdf-dark-mode-css');
                        if (darkModeLink) {
                            darkModeLink.disabled = !isDarkMode;
                        }
                    }
                } catch (error) {
                    console.error('Failed to toggle PDF dark mode:', error);
                }
            }
        });
    });
</script>
