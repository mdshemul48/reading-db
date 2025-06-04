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
    <script src="{{ asset('js/pdf-modules/color-manager.js') }}"></script>
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
    </script>
</body>

</html>
