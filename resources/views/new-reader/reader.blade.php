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

    <!-- PDF Reader Module Scripts -->
    <script src="{{ asset('js/pdf-modules/core.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/loader.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/renderer.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/scroll.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/progress.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/navigation.js') }}"></script>
    <script src="{{ asset('js/pdf-modules/ui-events.js') }}"></script>
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
                <button id="dark-mode-toggle" class="btn" title="Toggle Dark Mode">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

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
    </script>
</body>

</html>
