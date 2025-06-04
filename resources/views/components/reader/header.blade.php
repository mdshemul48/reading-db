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
