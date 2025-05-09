<x-app-layout>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

                <button id="toggle-annotations"
                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full"
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

    <!-- Annotation Panel -->
    <div id="annotation-panel"
        class="hidden fixed right-0 top-0 bottom-0 w-80 bg-white shadow-lg z-40 transform transition-transform duration-300 ease-in-out"
        style="height: 100vh;">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="font-semibold text-lg">Annotations</h3>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500">Color:</span>
                <select id="highlight-color" class="border rounded px-2 py-1 text-xs">
                    <option value="#ffff00" style="background-color: #ffff00">Yellow</option>
                    <option value="#90ee90" style="background-color: #90ee90">Green</option>
                    <option value="#add8e6" style="background-color: #add8e6">Blue</option>
                    <option value="#ffb6c1" style="background-color: #ffb6c1">Pink</option>
                </select>
                <button id="close-annotation-panel" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-4 overflow-y-auto" style="height: calc(100vh - 64px);">
            <div id="annotations-container" class="space-y-4">
                <!-- Annotations will be loaded here -->
                <div class="text-center text-gray-500 py-8">
                    <p>No annotations yet</p>
                    <p class="text-sm mt-2">Highlight text in the PDF to create one</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Annotation tooltip that appears when text is selected -->
    <div id="annotation-tooltip" class="hidden fixed bg-white rounded shadow-lg z-50 border border-gray-200">
        <div class="flex p-1 items-center">
            <div class="color-options flex space-x-1 mr-2">
                <button class="color-option w-5 h-5 rounded-full border border-gray-300" data-color="#ffff00"
                    style="background-color: #ffff00;" title="Yellow"></button>
                <button class="color-option w-5 h-5 rounded-full border border-gray-300" data-color="#90ee90"
                    style="background-color: #90ee90;" title="Green"></button>
                <button class="color-option w-5 h-5 rounded-full border border-gray-300" data-color="#add8e6"
                    style="background-color: #add8e6;" title="Blue"></button>
                <button class="color-option w-5 h-5 rounded-full border border-gray-300" data-color="#ffb6c1"
                    style="background-color: #ffb6c1;" title="Pink"></button>
            </div>
            <button id="highlight-btn" class="p-2 rounded hover:bg-gray-100" title="Highlight">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </button>
            <button id="note-btn" class="p-2 rounded hover:bg-gray-100" title="Add Note">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button id="pronounce-tooltip-btn" class="p-2 rounded hover:bg-gray-100" title="Pronounce">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.536a5 5 0 001.414 1.414m5.656-5.656a2 2 0 010 2.828m-5.656 0a2 2 0 010-2.828m8.486-8.486a2 2 0 013.536 0" />
                </svg>
            </button>
            <button id="search-definition-btn" class="p-2 rounded hover:bg-gray-100" title="Search Definition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z" />
                </svg>
            </button>
            <button id="search-web-btn" class="p-2 rounded hover:bg-gray-100" title="Search on Google">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
            </button>
            <button id="save-vocabulary-btn" class="p-2 rounded hover:bg-gray-100" title="Save to Vocabulary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </button>
            <button id="dictionary-lookup-btn" class="p-2 rounded hover:bg-gray-100" title="Look Up in Dictionary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 6l9 4 9-4M3 10l9 4 9-4m-9-4v12" />
                </svg>
            </button>
        </div>
        <!-- Audio status indicator for pronunciation -->
        <div id="tooltip-audio-status" class="hidden text-center py-1 text-xs text-gray-500 bg-gray-100 w-full">
            <span class="loading">
                <svg class="animate-spin h-3 w-3 text-blue-500 inline mr-1" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Loading audio...
            </span>
        </div>
    </div>

    <!-- Dictionary Modal -->
    <div id="dictionary-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2" id="dictionary-word">
                                <!-- Word will be displayed here -->
                            </h3>
                            <div class="mt-2">
                                <div id="dictionary-loading" class="text-center py-4">
                                    <svg class="animate-spin h-6 w-6 mx-auto text-gray-500"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">Loading definition...</p>
                                </div>
                                <div id="dictionary-content" class="overflow-y-auto max-h-96">
                                    <!-- Dictionary content will be displayed here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="save-to-vocabulary-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save to Vocabulary
                    </button>
                    <button type="button" id="close-dictionary-modal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Vocabulary Modal -->
    <div id="vocabulary-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">
                                Save Word to Vocabulary
                            </h3>
                            <div class="mt-2">
                                <div class="mb-4">
                                    <label for="vocabulary-word"
                                        class="block text-sm font-medium text-gray-700">Word</label>
                                    <input type="text" name="vocabulary-word" id="vocabulary-word"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div class="mb-4">
                                    <label for="vocabulary-definition"
                                        class="block text-sm font-medium text-gray-700">Definition</label>
                                    <textarea name="vocabulary-definition" id="vocabulary-definition" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="vocabulary-context"
                                        class="block text-sm font-medium text-gray-700">Context (from the book)</label>
                                    <textarea name="vocabulary-context" id="vocabulary-context" rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="vocabulary-notes"
                                        class="block text-sm font-medium text-gray-700">Personal Notes</label>
                                    <textarea name="vocabulary-notes" id="vocabulary-notes" rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Book</label>
                                    <input type="hidden" name="vocabulary-book" id="vocabulary-book"
                                        value="{{ $book->id }}">
                                    <div class="mt-1 p-2 bg-gray-50 rounded-md text-gray-700">{{ $book->title }}
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="vocabulary-page" class="block text-sm font-medium text-gray-700">Page
                                        Number</label>
                                    <input type="number" name="vocabulary-page" id="vocabulary-page"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Difficulty Level</label>
                                    <div class="mt-2 flex justify-between">
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio" name="difficulty"
                                                value="easy" checked>
                                            <span class="ml-2 text-green-600">Easy</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio" name="difficulty"
                                                value="medium">
                                            <span class="ml-2 text-yellow-600">Medium</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio" name="difficulty"
                                                value="hard">
                                            <span class="ml-2 text-red-600">Hard</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="save-vocabulary"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button" id="close-vocabulary-modal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Note editor dialog -->
    <div id="note-editor"
        class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl z-50 w-96">
        <div class="p-4 border-b">
            <h3 class="font-semibold">Add Note</h3>
        </div>
        <div class="p-4">
            <textarea id="note-text" class="w-full h-32 border rounded p-2" placeholder="Enter your note here..."></textarea>
            <div class="mt-2 text-sm text-gray-500">
                <p>Text: <span id="highlighted-text" class="italic"></span></p>
            </div>
        </div>
        <div class="p-4 bg-gray-50 flex justify-end space-x-2 rounded-b-lg">
            <button id="cancel-note" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-100">Cancel</button>
            <button id="save-note" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
        </div>
    </div>

    <!-- Highlight note popup -->
    <div id="highlight-note-popup"
        class="hidden fixed bg-white rounded-lg shadow-xl z-50 border border-gray-200 max-w-sm">
        <div class="p-3">
            <div class="flex justify-between items-start mb-2">
                <div class="font-medium" id="popup-text-content"></div>
                <button id="close-note-popup" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="popup-note-content" class="text-sm border-l-2 border-gray-300 pl-2 italic"></div>
            <div class="mt-2 flex justify-end space-x-2">
                <button id="popup-edit-note"
                    class="text-xs px-2 py-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100">Edit</button>
                <button id="popup-delete-note"
                    class="text-xs px-2 py-1 bg-red-50 text-red-600 rounded hover:bg-red-100">Delete</button>
            </div>
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
                let currentBookId = {{ $book->id ?? 'null' }};
                let currentSelection = null;
                let allAnnotations = [];
                let currentWord = "";
                let currentDefinition = "";

                // Annotation panel elements
                const annotationPanel = document.getElementById('annotation-panel');
                const toggleAnnotationsBtn = document.getElementById('toggle-annotations');
                const closeAnnotationPanelBtn = document.getElementById('close-annotation-panel');
                const annotationsContainer = document.getElementById('annotations-container');
                const highlightColorSelect = document.getElementById('highlight-color');

                // Annotation tooltip elements
                const annotationTooltip = document.getElementById('annotation-tooltip');
                const highlightBtn = document.getElementById('highlight-btn');
                const noteBtn = document.getElementById('note-btn');
                const searchDefinitionBtn = document.getElementById('search-definition-btn');
                const searchWebBtn = document.getElementById('search-web-btn');
                const saveVocabularyBtn = document.getElementById('save-vocabulary-btn');
                const dictionaryLookupBtn = document.getElementById('dictionary-lookup-btn');
                const pronounceTooltipBtn = document.getElementById('pronounce-tooltip-btn');
                const tooltipAudioStatus = document.getElementById('tooltip-audio-status');
                const colorOptions = document.querySelectorAll('.color-option');
                let selectedHighlightColor = '#ffff00'; // Default color

                // Dictionary modal elements
                const dictionaryModal = document.getElementById('dictionary-modal');
                const dictionaryWord = document.getElementById('dictionary-word');
                const dictionaryLoading = document.getElementById('dictionary-loading');
                const dictionaryContent = document.getElementById('dictionary-content');
                const closeDictionaryModalBtn = document.getElementById('close-dictionary-modal');
                const saveToVocabularyBtn = document.getElementById('save-to-vocabulary-btn');

                // Vocabulary modal elements
                const vocabularyModal = document.getElementById('vocabulary-modal');
                const vocabularyWord = document.getElementById('vocabulary-word');
                const vocabularyDefinition = document.getElementById('vocabulary-definition');
                const vocabularyContext = document.getElementById('vocabulary-context');
                const vocabularyNotes = document.getElementById('vocabulary-notes');
                const saveVocabularyModalBtn = document.getElementById('save-vocabulary');
                const closeVocabularyModalBtn = document.getElementById('close-vocabulary-modal');

                // Note editor elements
                const noteEditor = document.getElementById('note-editor');
                const noteText = document.getElementById('note-text');
                const highlightedText = document.getElementById('highlighted-text');
                const saveNoteBtn = document.getElementById('save-note');
                const cancelNoteBtn = document.getElementById('cancel-note');

                // Note popup elements
                const notePopup = document.getElementById('highlight-note-popup');
                const popupTextContent = document.getElementById('popup-text-content');
                const popupNoteContent = document.getElementById('popup-note-content');
                const closeNotePopupBtn = document.getElementById('close-note-popup');
                const popupEditNoteBtn = document.getElementById('popup-edit-note');
                const popupDeleteNoteBtn = document.getElementById('popup-delete-note');
                let currentAnnotationId = null;

                // Toggle annotation panel
                toggleAnnotationsBtn.addEventListener('click', function() {
                    annotationPanel.classList.toggle('hidden');
                    if (!annotationPanel.classList.contains('hidden')) {
                        loadAnnotations();
                    }
                });

                closeAnnotationPanelBtn.addEventListener('click', function() {
                    annotationPanel.classList.add('hidden');
                });

                // Load annotations from the server
                function loadAnnotations() {
                    console.log("Loading annotations...");
                    fetch(`{{ route('books.annotations', $book) }}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                allAnnotations = data.annotations;
                                console.log("Loaded annotations:", allAnnotations);
                                renderAnnotations();
                                applyAnnotationsToDocument();
                            }
                        })
                        .catch(error => {
                            console.error('Error loading annotations:', error);
                        });
                }

                // Render annotations in the sidebar
                function renderAnnotations() {
                    if (allAnnotations.length === 0) {
                        annotationsContainer.innerHTML = `
                            <div class="text-center text-gray-500 py-8">
                                <p>No annotations yet</p>
                                <p class="text-sm mt-2">Highlight text in the PDF to create one</p>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    allAnnotations.forEach(annotation => {
                        const truncatedText = annotation.text_content ?
                            (annotation.text_content.length > 100 ?
                                annotation.text_content.substring(0, 100) + '...' :
                                annotation.text_content) : '';

                        html += `
                            <div class="annotation-item p-3 border rounded hover:bg-gray-50" data-id="${annotation.id}">
                                <div class="flex justify-between items-start">
                                    <div class="font-medium">Page ${annotation.page_number}</div>
                                    <div class="flex space-x-1">
                                        <button class="edit-annotation text-gray-500 hover:text-gray-700 p-1" data-id="${annotation.id}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button class="delete-annotation text-gray-500 hover:text-red-500 p-1" data-id="${annotation.id}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <div class="text-sm" style="background-color: ${annotation.color || '#ffff00'}33; padding: 2px 4px; border-radius: 2px;">
                                        ${truncatedText}
                                    </div>
                                    ${annotation.note ? `<div class="mt-1 text-sm border-l-2 border-gray-300 pl-2 italic">${annotation.note}</div>` : ''}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    ${new Date(annotation.created_at).toLocaleString()}
                                </div>
                            </div>
                        `;
                    });

                    annotationsContainer.innerHTML = html;

                    // Add event listeners to annotation items
                    document.querySelectorAll('.delete-annotation').forEach(button => {
                        button.addEventListener('click', function(e) {
                            e.preventDefault();
                            const annotationId = this.getAttribute('data-id');
                            deleteAnnotation(annotationId);
                        });
                    });

                    document.querySelectorAll('.edit-annotation').forEach(button => {
                        button.addEventListener('click', function(e) {
                            e.preventDefault();
                            const annotationId = this.getAttribute('data-id');
                            const annotation = allAnnotations.find(a => a.id == annotationId);
                            if (annotation) {
                                openNoteEditor(annotation);
                            }
                        });
                    });

                    // Jump to page when clicking an annotation
                    document.querySelectorAll('.annotation-item').forEach(item => {
                        item.addEventListener('click', function(e) {
                            if (!e.target.closest('button')) {
                                const annotationId = this.getAttribute('data-id');
                                const annotation = allAnnotations.find(a => a.id == annotationId);
                                if (annotation) {
                                    jumpToAnnotation(annotation);
                                }
                            }
                        });
                    });
                }

                // Apply annotations to the PDF document
                function applyAnnotationsToDocument() {
                    try {
                        const frameWindow = pdfViewer.contentWindow;
                        if (!frameWindow.PDFViewerApplication || !frameWindow.PDFViewerApplication.initialized) {
                            console.log("PDFViewerApplication not ready, retrying in 500ms...");
                            setTimeout(applyAnnotationsToDocument, 500);
                            return;
                        }

                        console.log("Applying annotations to document", allAnnotations);

                        // Direct manipulation of PDF.js internals to apply highlights
                        const script = frameWindow.document.createElement('script');
                        script.textContent = `
                            (function() {
                                console.log("Starting highlight application process");
                                // Add our custom highlight handler directly into PDF.js
                                const PDFViewerApplication = window.PDFViewerApplication;

                                // Clear any previous annotations first
                                const clearExistingAnnotations = () => {
                                    document.querySelectorAll('.pdf-annotation-highlight').forEach(el => el.remove());
                                };
                                clearExistingAnnotations();

                                // Get all of our annotations
                                const annotations = ${JSON.stringify(allAnnotations)};
                                console.log("Processing", annotations.length, "annotations");

                                // Add utility function to find visible pages
                                function getVisiblePages() {
                                    const container = PDFViewerApplication.pdfViewer.container;
                                    const pages = PDFViewerApplication.pdfViewer._pages;
                                    const visiblePages = [];

                                    pages.forEach(page => {
                                        if (page.div && page.div.offsetParent !== null) {
                                            visiblePages.push(page);
                                        }
                                    });

                                    return visiblePages;
                                }

                                // Function to highlight a specific annotation
                                function addHighlightToPage(annotation, pageView) {
                                    try {
                                        let positionData = typeof annotation.position_data === 'string'
                                            ? JSON.parse(annotation.position_data)
                                            : annotation.position_data;

                                        if (!positionData) {
                                            console.warn("No position data for annotation", annotation.id);
                                            return;
                                        }

                                        console.log("Processing annotation:", annotation.id, "on page", annotation.page_number);
                                        console.log("Position data:", positionData);

                                        // Get the current scale to adjust position
                                        const currentScale = PDFViewerApplication.pdfViewer.currentScale || 1;
                                        const storedScale = positionData.scale || 1;
                                        const scaleFactor = currentScale / storedScale;

                                        // Get text layer for better positioning
                                        const textLayer = pageView.textLayer?.textLayerDiv || pageView.div.querySelector('.textLayer');
                                        const canvasWrapper = pageView.div.querySelector('.canvasWrapper');

                                        // If we have extreme values, they might be in a different coordinate system
                                        // Adjust as needed
                                        if (positionData.top > pageView.div.clientHeight * 2) {
                                            // This might be in a different coordinate system - attempt to normalize
                                            const pageHeight = pageView.viewport.height;
                                            const pageWidth = pageView.viewport.width;

                                            console.log("Large position values detected - attempting to normalize");
                                            console.log("Page dimensions:", { width: pageWidth, height: pageHeight });
                                            console.log("Current scale:", currentScale);

                                            // Try to normalize the coordinates - dividing by a larger factor since values are very large
                                            positionData = {
                                                left: (positionData.left / 50) * currentScale,
                                                top: (positionData.top / 50) * currentScale,
                                                width: (positionData.width / 10) * currentScale,
                                                height: (positionData.height / 3) * currentScale
                                            };

                                            console.log("Normalized position:", positionData);
                                        } else {
                                            // Standard scaling
                                            positionData = {
                                                left: positionData.left * scaleFactor,
                                                top: positionData.top * scaleFactor,
                                                width: positionData.width * scaleFactor,
                                                height: positionData.height * scaleFactor
                                            };
                                        }

                                        // Create the highlight element
                                        const highlightColor = annotation.color || '#ffff00';
                                        const div = document.createElement('div');
                                        div.className = 'pdf-annotation-highlight';
                                        div.setAttribute('data-annotation-id', annotation.id);
                                        div.style.position = 'absolute';
                                        div.style.backgroundColor = highlightColor;
                                        div.style.mixBlendMode = 'multiply';
                                        div.style.opacity = '0.9';
                                        div.style.zIndex = '1';
                                        div.style.pointerEvents = 'auto'; // Allow clicks on highlights
                                        div.style.cursor = 'pointer'; // Show pointer cursor on hover

                                        // Store annotation data as an attribute for easy access
                                        div.setAttribute('data-annotation-text', annotation.text_content || '');
                                        div.setAttribute('data-annotation-note', annotation.note || '');
                                        div.setAttribute('data-annotation-color', annotation.color || '#ffff00');
                                        div.setAttribute('data-annotation-page', annotation.page_number);

                                        // Add click event to show note popup
                                        div.addEventListener('click', function(e) {
                                            e.stopPropagation(); // Prevent triggering PDF.js click handlers
                                            const annotationId = this.getAttribute('data-annotation-id');
                                            const annotationText = this.getAttribute('data-annotation-text');
                                            const annotationNote = this.getAttribute('data-annotation-note');

                                            // Send message to parent window to show popup
                                            window.parent.postMessage({
                                                type: 'showNotePopup',
                                                id: annotationId,
                                                text: annotationText,
                                                note: annotationNote,
                                                rect: this.getBoundingClientRect(),
                                                pageRect: pageView.div.getBoundingClientRect()
                                            }, '*');
                                        });

                                        // Position - adjust based on text content to improve alignment
                                        // Find text nodes that contain this text and use their position if possible
                                        if (textLayer && annotation.text_content) {
                                            const textNodes = Array.from(textLayer.querySelectorAll('.textLayer > span'));

                                            for (const node of textNodes) {
                                                if (node.textContent && node.textContent.includes(annotation.text_content.substring(0, 20))) {
                                                    // Found text node that contains our highlight text
                                                    const nodeRect = node.getBoundingClientRect();
                                                    const layerRect = textLayer.getBoundingClientRect();

                                                    // Use this position instead
                                                    positionData = {
                                                        left: node.offsetLeft,
                                                        top: node.offsetTop,
                                                        width: node.offsetWidth,
                                                        height: node.offsetHeight
                                                    };

                                                    console.log("Using text node position for highlight:", positionData);
                                                    break;
                                                }
                                            }
                                        }

                                        // Position the highlight
                                        div.style.left = positionData.left + 'px';
                                        div.style.top = positionData.top + 'px';
                                        div.style.width = positionData.width + 'px';
                                        div.style.height = positionData.height + 'px';

                                        // Add the highlight to the page
                                        if (textLayer) {
                                            textLayer.appendChild(div); // Add to text layer for better positioning
                                        } else {
                                            pageView.div.appendChild(div);
                                        }

                                        console.log("Added highlight to page", annotation.page_number, div);
                                        return div;
                                    } catch (e) {
                                        console.error("Error adding highlight:", e, annotation);
                                        return null;
                                    }
                                }

                                // Apply highlights to all pages with annotations
                                function applyHighlightsToAllAnnotations() {
                                    // Group annotations by page
                                    const annotationsByPage = {};
                                    annotations.forEach(a => {
                                        if (!annotationsByPage[a.page_number]) {
                                            annotationsByPage[a.page_number] = [];
                                        }
                                        annotationsByPage[a.page_number].push(a);
                                    });

                                    // Apply to each page that has annotations
                                    Object.keys(annotationsByPage).forEach(pageNumber => {
                                        const pageIndex = parseInt(pageNumber) - 1;
                                        if (pageIndex < 0 || pageIndex >= PDFViewerApplication.pdfViewer._pages.length) {
                                            console.warn("Page index out of bounds:", pageIndex);
                                            return;
                                        }

                                        const pageView = PDFViewerApplication.pdfViewer._pages[pageIndex];
                                        if (!pageView || !pageView.div) {
                                            console.warn("Page view not found for page", pageNumber);
                                            return;
                                        }

                                        // Remove any existing highlights for this page
                                        pageView.div.querySelectorAll('.pdf-annotation-highlight').forEach(el => el.remove());

                                        // Add each annotation highlight
                                        annotationsByPage[pageNumber].forEach(annotation => {
                                            addHighlightToPage(annotation, pageView);
                                        });
                                    });
                                }

                                // Apply highlights immediately
                                applyHighlightsToAllAnnotations();

                                // Listen for page render events to reapply highlights
                                PDFViewerApplication.eventBus.on('pagerendered', (evt) => {
                                    const pageNumber = evt.pageNumber;
                                    const pageIndex = pageNumber - 1;
                                    const pageView = PDFViewerApplication.pdfViewer._pages[pageIndex];

                                    if (!pageView) return;

                                    // Find annotations for this page
                                    const pageAnnotations = annotations.filter(a => a.page_number === pageNumber);
                                    if (pageAnnotations.length === 0) return;

                                    // Remove existing highlights
                                    pageView.div.querySelectorAll('.pdf-annotation-highlight').forEach(el => el.remove());

                                    // Add highlights
                                    pageAnnotations.forEach(annotation => {
                                        addHighlightToPage(annotation, pageView);
                                    });
                                });

                                // Also listen for scale changes (zoom)
                                PDFViewerApplication.eventBus.on('scalechanged', () => {
                                    console.log("Scale changed, reapplying highlights");
                                    applyHighlightsToAllAnnotations();
                                });
                            })();
                        `;

                        // Inject our script directly into the PDF.js iframe
                        frameWindow.document.body.appendChild(script);

                        // Also add some CSS to make highlights more visible
                        const styleEl = frameWindow.document.createElement('style');
                        styleEl.textContent = `
                            .pdf-annotation-highlight {
                                box-shadow: 0 0 5px rgba(0,0,0,0.3);
                                border-radius: 2px;
                                mix-blend-mode: multiply;
                                transition: opacity 0.2s;
                            }
                            .pdf-annotation-highlight:hover {
                                opacity: 0.7 !important;
                                box-shadow: 0 0 8px rgba(0,0,0,0.5);
                            }
                        `;
                        frameWindow.document.head.appendChild(styleEl);

                    } catch (err) {
                        console.error('Error applying annotations:', err);
                    }
                }

                // Jump to a specific annotation
                function jumpToAnnotation(annotation) {
                    try {
                        const frameWindow = pdfViewer.contentWindow;
                        if (!frameWindow.PDFViewerApplication) return;

                        // Navigate to the page
                        frameWindow.PDFViewerApplication.page = annotation.page_number;

                        // Scroll to annotation after page is rendered
                        setTimeout(() => {
                            const annotationElement = frameWindow.document.querySelector(
                                `[data-annotation-id="${annotation.id}"]`
                            );

                            if (annotationElement) {
                                annotationElement.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                                // Flash the annotation
                                const originalBg = annotationElement.style.backgroundColor;
                                annotationElement.style.backgroundColor = '#ff0000'; // Flash red
                                setTimeout(() => {
                                    annotationElement.style.backgroundColor = originalBg;
                                }, 500);
                            }
                        }, 500);
                    } catch (err) {
                        console.error('Error jumping to annotation:', err);
                    }
                }

                // Delete an annotation
                function deleteAnnotation(annotationId) {
                    if (!confirm('Are you sure you want to delete this annotation?')) return;

                    fetch(`{{ url('/annotations') }}/${annotationId}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove from array
                                allAnnotations = allAnnotations.filter(a => a.id != annotationId);
                                renderAnnotations();

                                // Remove from PDF
                                try {
                                    const frameWindow = pdfViewer.contentWindow;
                                    if (frameWindow.document) {
                                        const annotationElement = frameWindow.document.querySelector(
                                            `[data-annotation-id="${annotationId}"]`
                                        );
                                        if (annotationElement) {
                                            annotationElement.remove();
                                        }
                                    }
                                } catch (err) {
                                    console.error('Error removing annotation element:', err);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting annotation:', error);
                        });
                }

                // Open note editor for annotation
                function openNoteEditor(annotation = null) {
                    if (annotation) {
                        // Edit existing annotation
                        noteText.value = annotation.note || '';
                        highlightedText.textContent = annotation.text_content || '';
                        noteEditor.setAttribute('data-annotation-id', annotation.id);
                    } else {
                        // New annotation
                        noteText.value = '';
                        highlightedText.textContent = currentSelection?.toString() || '';
                        noteEditor.removeAttribute('data-annotation-id');
                    }

                    noteEditor.classList.remove('hidden');
                }

                // Save note
                saveNoteBtn.addEventListener('click', function() {
                    const annotationId = noteEditor.getAttribute('data-annotation-id');

                    if (annotationId) {
                        // Update existing annotation
                        updateAnnotation(annotationId, noteText.value);
                    } else {
                        // Create new annotation with note
                        createAnnotation('note');
                    }

                    noteEditor.classList.add('hidden');
                });

                // Cancel note
                cancelNoteBtn.addEventListener('click', function() {
                    noteEditor.classList.add('hidden');
                });

                // Update an annotation
                function updateAnnotation(annotationId, note) {
                    fetch(`{{ url('/annotations') }}/${annotationId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                note: note
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update in array
                                const index = allAnnotations.findIndex(a => a.id == annotationId);
                                if (index !== -1) {
                                    allAnnotations[index] = data.annotation;
                                    renderAnnotations();
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error updating annotation:', error);
                        });
                }

                // Create an annotation
                function createAnnotation(type) {
                    if (!currentSelection) {
                        console.error('No text selected');
                        return;
                    }

                    try {
                        const frameWindow = pdfViewer.contentWindow;
                        if (!frameWindow.PDFViewerApplication) return;

                        const PDFViewerApplication = frameWindow.PDFViewerApplication;
                        const currentPage = PDFViewerApplication.page;
                        const selectedText = currentSelection.toString();

                        // Get position data for highlighting
                        let positionData = {};
                        const range = currentSelection.getRangeAt(0);
                        const rects = range.getClientRects();
                        const rect = range.getBoundingClientRect();

                        // Get the position relative to the PDF viewer
                        const viewerContainer = frameWindow.document.getElementById('viewerContainer');
                        const viewer = frameWindow.document.getElementById('viewer');
                        const pageDiv = frameWindow.document.querySelector('.page[data-page-number="' + currentPage +
                            '"]');

                        if (!pageDiv) {
                            console.error('Page div not found');
                            return;
                        }

                        const pageBounds = pageDiv.getBoundingClientRect();
                        const viewerBounds = viewer.getBoundingClientRect();

                        // Calculate position relative to the page
                        positionData = {
                            left: rect.left - pageBounds.left,
                            top: rect.top - pageBounds.top,
                            width: rect.width,
                            height: rect.height,
                            // Store additional data that might help with positioning
                            pageWidth: pageBounds.width,
                            pageHeight: pageBounds.height,
                            scale: PDFViewerApplication.pdfViewer.currentScale || 1
                        };

                        // Also store all client rects for multi-line selections
                        if (rects.length > 1) {
                            positionData.rects = Array.from(rects).map(r => ({
                                left: r.left - pageBounds.left,
                                top: r.top - pageBounds.top,
                                width: r.width,
                                height: r.height
                            }));
                        }

                        console.log("Creating annotation with position:", positionData);
                        console.log("Page bounds:", pageBounds);
                        console.log("Selection rect:", rect);

                        // Create annotation
                        fetch(`{{ route('books.annotations.store', $book) }}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    page_number: currentPage,
                                    text_content: selectedText,
                                    annotation_type: type,
                                    note: type === 'note' ? noteText.value : null,
                                    position_data: JSON.stringify(positionData),
                                    color: selectedHighlightColor // Use selected color from tooltip
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Add to array
                                    allAnnotations.push(data.annotation);
                                    renderAnnotations();

                                    // Apply to PDF
                                    applyAnnotationsToDocument();

                                    // Clear selection
                                    if (frameWindow.getSelection) {
                                        frameWindow.getSelection().removeAllRanges();
                                    }
                                    currentSelection = null;
                                }
                            })
                            .catch(error => {
                                console.error('Error creating annotation:', error);
                            });
                    } catch (err) {
                        console.error('Error creating annotation:', err);
                    }
                }

                // Hide tooltip when clicking outside
                document.addEventListener('mousedown', function(e) {
                    if (!annotationTooltip.contains(e.target) && !e.target.closest(
                            '.pdf-annotation-highlight')) {
                        annotationTooltip.classList.add('hidden');
                    }
                });

                // Also hide tooltip when text selection is cleared
                document.addEventListener('selectionchange', function() {
                    const selection = window.getSelection();
                    if (selection.toString().trim().length === 0 && !annotationTooltip.classList.contains(
                            'hidden')) {
                        // Small delay to allow buttons in tooltip to be clicked
                        setTimeout(() => {
                            if (!annotationTooltip.matches(':hover')) {
                                annotationTooltip.classList.add('hidden');
                            }
                        }, 100);
                    }
                });

                // Listen for selection changes in the PDF iframe
                function injectTextSelectionHandler() {
                    try {
                        const frameWindow = pdfViewer.contentWindow;
                        if (!frameWindow.PDFViewerApplication || !frameWindow.PDFViewerApplication.initialized) {
                            setTimeout(injectTextSelectionHandler, 500);
                            return;
                        }

                        const frameDoc = frameWindow.document;
                        const script = frameDoc.createElement('script');
                        script.textContent = `
                            (function() {
                                if (window.textSelectionHandlerInjected) return;
                                window.textSelectionHandlerInjected = true;

                                // Listen for text selection
                                document.addEventListener('mouseup', function(e) {
                                    const selection = window.getSelection();

                                    if (selection.toString().trim().length > 0) {
                                        // Send selection to parent window
                                        window.parent.postMessage({
                                            type: 'textSelection',
                                            selection: selection.toString(),
                                            rect: selection.getRangeAt(0).getBoundingClientRect()
                                        }, '*');
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
                            })();
                        `;

                        frameDoc.body.appendChild(script);
                    } catch (err) {
                        console.error('Error injecting text selection handler:', err);
                    }
                }

                // Listen for text selection from PDF.js
                window.addEventListener('message', function(e) {
                    if (e.source !== pdfViewer.contentWindow) return;

                    const message = e.data;

                    if (message && message.type === 'textSelection') {
                        // Show annotation tooltip
                        const rect = message.rect;
                        const viewerRect = pdfViewer.getBoundingClientRect();

                        // Position tooltip near the selection
                        annotationTooltip.style.left = (viewerRect.left + rect.left) + 'px';
                        annotationTooltip.style.top = (viewerRect.top + rect.bottom + 10) + 'px';
                        annotationTooltip.classList.remove('hidden');

                        // Store selection
                        currentSelection = pdfViewer.contentWindow.getSelection();
                    } else if (message && message.type === 'selectionCleared') {
                        // Hide tooltip when selection is cleared in iframe
                        annotationTooltip.classList.add('hidden');
                        currentSelection = null;
                    } else if (message && message.type === 'showNotePopup') {
                        // Show the note popup
                        const annotationId = message.id;
                        const annotationText = message.text;
                        const annotationNote = message.note;
                        const rect = message.rect;
                        const pageRect = message.pageRect;

                        // Position popup near the highlight
                        const viewerRect = pdfViewer.getBoundingClientRect();
                        let left = viewerRect.left + rect.left;
                        let top = viewerRect.top + rect.bottom + 10;

                        // Make sure popup stays within viewport
                        const popupWidth = 300; // Approximate width of popup
                        if (left + popupWidth > window.innerWidth) {
                            left = window.innerWidth - popupWidth - 20;
                        }

                        notePopup.style.left = left + 'px';
                        notePopup.style.top = top + 'px';

                        // Set content
                        popupTextContent.textContent = annotationText;

                        if (annotationNote && annotationNote.trim() !== '') {
                            popupNoteContent.textContent = annotationNote;
                            popupNoteContent.classList.remove('hidden');
                        } else {
                            popupNoteContent.classList.add('hidden');
                        }

                        // Store current annotation ID
                        currentAnnotationId = annotationId;

                        // Show popup
                        notePopup.classList.remove('hidden');
                    }
                });

                // Highlight button
                highlightBtn.addEventListener('click', function() {
                    createAnnotation('highlight');
                    annotationTooltip.classList.add('hidden');
                });

                // Note button
                noteBtn.addEventListener('click', function() {
                    openNoteEditor();
                    annotationTooltip.classList.add('hidden');
                });

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

                    // Inject text selection handler for annotations
                    injectTextSelectionHandler();

                    // Load and apply annotations whenever the PDF is reloaded
                    // Using a longer delay to ensure PDF.js is fully initialized
                    setTimeout(() => {
                        loadAnnotations();
                    }, 1500);
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

                // Add event listeners to color options
                colorOptions.forEach(option => {
                    option.addEventListener('click', function(e) {
                        e.preventDefault();
                        // Remove active state from all options
                        colorOptions.forEach(opt => opt.classList.remove('ring-2', 'ring-blue-500'));
                        // Add active state to selected option
                        this.classList.add('ring-2', 'ring-blue-500');
                        // Set selected color
                        selectedHighlightColor = this.getAttribute('data-color');
                        // Update the highlight color in the dropdown menu too
                        highlightColorSelect.value = selectedHighlightColor;
                    });
                });

                // Mark default color as selected
                document.querySelector(`.color-option[data-color="#ffff00"]`).classList.add('ring-2', 'ring-blue-500');

                // Add event listeners for the note popup
                closeNotePopupBtn.addEventListener('click', function() {
                    notePopup.classList.add('hidden');
                });

                popupEditNoteBtn.addEventListener('click', function() {
                    // Find the annotation
                    const annotation = allAnnotations.find(a => a.id == currentAnnotationId);
                    if (annotation) {
                        openNoteEditor(annotation);
                        notePopup.classList.add('hidden');
                    }
                });

                popupDeleteNoteBtn.addEventListener('click', function() {
                    if (currentAnnotationId) {
                        deleteAnnotation(currentAnnotationId);
                        notePopup.classList.add('hidden');
                    }
                });

                // Click outside to close popup
                document.addEventListener('mousedown', function(e) {
                    if (!notePopup.contains(e.target) && !e.target.closest('.pdf-annotation-highlight')) {
                        notePopup.classList.add('hidden');
                    }
                });

                // Add event listeners for search functionality
                searchDefinitionBtn.addEventListener('click', function() {
                    if (!currentSelection) return;

                    const selectedText = currentSelection.toString().trim();
                    if (selectedText.length === 0) return;

                    // Create a Google Dictionary search URL
                    const searchUrl =
                        `https://www.google.com/search?q=define+${encodeURIComponent(selectedText)}`;

                    // Open in a new tab
                    window.open(searchUrl, '_blank');

                    // Hide tooltip
                    annotationTooltip.classList.add('hidden');
                });

                searchWebBtn.addEventListener('click', function() {
                    if (!currentSelection) return;

                    const selectedText = currentSelection.toString().trim();
                    if (selectedText.length === 0) return;

                    // Create a regular Google search URL
                    const searchUrl = `https://www.google.com/search?q=${encodeURIComponent(selectedText)}`;

                    // Open in a new tab
                    window.open(searchUrl, '_blank');

                    // Hide tooltip
                    annotationTooltip.classList.add('hidden');
                });

                // Add event listeners for vocabulary functionality
                saveVocabularyBtn.addEventListener('click', function() {
                    if (!currentSelection) return;

                    const selectedText = currentSelection.toString().trim();
                    if (selectedText.length === 0) return;

                    // Set the selected word in the vocabulary modal
                    vocabularyWord.value = selectedText;

                    // Try to get the surrounding context (sentence)
                    try {
                        const range = currentSelection.getRangeAt(0);
                        const selectedNode = range.startContainer;

                        // Get parent paragraph or closest text container
                        let contextNode = selectedNode;
                        if (selectedNode.nodeType === Node.TEXT_NODE) {
                            contextNode = selectedNode.parentNode;
                        }

                        // Get text from parent for context
                        let context = contextNode.textContent.trim();
                        if (context.length > 200) {
                            // Truncate long context, trying to keep the selected word in the middle
                            const wordIndex = context.indexOf(selectedText);
                            if (wordIndex >= 0) {
                                const startPos = Math.max(0, wordIndex - 80);
                                const endPos = Math.min(context.length, wordIndex + selectedText.length + 80);
                                context = (startPos > 0 ? '...' : '') +
                                    context.substring(startPos, endPos) +
                                    (endPos < context.length ? '...' : '');
                            } else {
                                context = context.substring(0, 200) + '...';
                            }
                        }

                        vocabularyContext.value = context;
                    } catch (e) {
                        console.error('Error getting context:', e);
                        vocabularyContext.value = '';
                    }

                    // Set current page number
                    const currentPage = pdfViewer.contentWindow.PDFViewerApplication?.page || 1;
                    document.getElementById('vocabulary-page').value = currentPage;

                    // Show the vocabulary modal
                    vocabularyModal.classList.remove('hidden');

                    // Hide tooltip
                    annotationTooltip.classList.add('hidden');
                });

                dictionaryLookupBtn.addEventListener('click', function() {
                    if (!currentSelection) return;

                    const selectedText = currentSelection.toString().trim();
                    if (selectedText.length === 0) return;

                    // Set the word in the dictionary modal
                    dictionaryWord.textContent = selectedText;
                    currentWord = selectedText;

                    // Show loading state
                    dictionaryLoading.classList.remove('hidden');
                    dictionaryContent.innerHTML = '';

                    // Show the dictionary modal
                    dictionaryModal.classList.remove('hidden');

                    // Hide tooltip
                    annotationTooltip.classList.add('hidden');

                    // Fetch definition from an API (Free Dictionary API)
                    fetch(`https://api.dictionaryapi.dev/api/v2/entries/en/${encodeURIComponent(selectedText)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Word not found in dictionary');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Process and display dictionary data
                            dictionaryLoading.classList.add('hidden');

                            let html = '';
                            if (data && data.length > 0) {
                                const entry = data[0];

                                // Phonetics
                                if (entry.phonetics && entry.phonetics.length > 0) {
                                    const phonetic = entry.phonetics.find(p => p.text) || entry.phonetics[
                                        0];
                                    if (phonetic) {
                                        html +=
                                            `<div class="text-gray-600 mb-3">${phonetic.text || ''}</div>`;
                                        if (phonetic.audio) {
                                            html += `<div class="mb-3">
                                                <audio controls src="${phonetic.audio}" class="w-full h-8">
                                                    Your browser does not support the audio element.
                                                </audio>
                                            </div>`;
                                        }
                                    }
                                }

                                // Meanings
                                if (entry.meanings && entry.meanings.length > 0) {
                                    entry.meanings.forEach(meaning => {
                                        html += `<div class="mb-4">
                                            <h4 class="font-semibold text-gray-800">${meaning.partOfSpeech}</h4>
                                            <ul class="list-disc pl-5 mt-2">`;

                                        // Definitions
                                        if (meaning.definitions && meaning.definitions.length > 0) {
                                            meaning.definitions.forEach(def => {
                                                html += `<li class="mb-2">
                                                    <p>${def.definition}</p>`;

                                                // Example
                                                if (def.example) {
                                                    html +=
                                                        `<p class="text-gray-600 italic mt-1">"${def.example}"</p>`;
                                                }

                                                html += `</li>`;
                                            });
                                        }

                                        html += `</ul>`;

                                        // Synonyms
                                        if (meaning.synonyms && meaning.synonyms.length > 0) {
                                            html += `<div class="mt-2">
                                                <span class="font-medium">Synonyms: </span>
                                                <span>${meaning.synonyms.join(', ')}</span>
                                            </div>`;
                                        }

                                        // Antonyms
                                        if (meaning.antonyms && meaning.antonyms.length > 0) {
                                            html += `<div class="mt-1">
                                                <span class="font-medium">Antonyms: </span>
                                                <span>${meaning.antonyms.join(', ')}</span>
                                            </div>`;
                                        }

                                        html += `</div>`;
                                    });
                                }

                                // Store definition for vocabulary
                                currentDefinition = entry.meanings
                                    .map(m => `(${m.partOfSpeech}) ` +
                                        m.definitions.slice(0, 1).map(d => d.definition).join('; '))
                                    .join(' ');
                            } else {
                                html = '<p class="text-gray-600">No definition found.</p>';
                                currentDefinition = '';
                            }

                            dictionaryContent.innerHTML = html;
                        })
                        .catch(error => {
                            console.error('Error fetching definition:', error);
                            dictionaryLoading.classList.add('hidden');
                            dictionaryContent.innerHTML = `
                                <div class="text-gray-600">
                                    <p>Sorry, we couldn't find a definition for "${selectedText}".</p>
                                    <p class="mt-2">You can still save this word to your vocabulary with your own definition.</p>
                                </div>
                            `;
                            currentDefinition = '';
                        });
                });

                // Dictionary modal close button
                closeDictionaryModalBtn.addEventListener('click', function() {
                    dictionaryModal.classList.add('hidden');
                });

                // Save to vocabulary from dictionary
                saveToVocabularyBtn.addEventListener('click', function() {
                    // Set values in vocabulary modal
                    vocabularyWord.value = currentWord;
                    vocabularyDefinition.value = currentDefinition;

                    // Set current page number
                    const currentPage = pdfViewer.contentWindow.PDFViewerApplication?.page || 1;
                    document.getElementById('vocabulary-page').value = currentPage;

                    // Hide dictionary modal and show vocabulary modal
                    dictionaryModal.classList.add('hidden');
                    vocabularyModal.classList.remove('hidden');
                });

                // Vocabulary modal close button
                closeVocabularyModalBtn.addEventListener('click', function() {
                    vocabularyModal.classList.add('hidden');
                });

                // Save vocabulary
                saveVocabularyModalBtn.addEventListener('click', function() {
                    // Ensure book_id is a number or null
                    const bookId = currentBookId ? parseInt(currentBookId) : null;

                    // Get selected difficulty, default to medium if none selected
                    const selectedDifficulty = document.querySelector('input[name="difficulty"]:checked')
                        ?.value || 'medium';

                    const wordData = {
                        word: vocabularyWord.value.trim(),
                        definition: vocabularyDefinition.value.trim() || null,
                        context: vocabularyContext.value.trim() || null,
                        notes: vocabularyNotes.value.trim() || null,
                        difficulty: selectedDifficulty,
                        book_id: bookId,
                        page_number: document.getElementById('vocabulary-page').value || pdfViewer
                            .contentWindow.PDFViewerApplication?.page || 1
                    };

                    if (!wordData.word) {
                        alert('Please enter a word.');
                        return;
                    }

                    // Get CSRF token from meta tag
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '{{ csrf_token() }}';

                    // Save to database via AJAX
                    fetch('{{ route('vocabulary.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(wordData)
                        })
                        .then(response => {
                            if (!response.ok) {
                                if (response.headers.get('content-type')?.includes('text/html')) {
                                    // This suggests we got a Laravel error page instead of JSON
                                    console.error(
                                        'Received HTML error response - CSRF token might be invalid');
                                    throw new Error('Server error - received HTML response');
                                }
                                return response.text().then(text => {
                                    try {
                                        // Try to parse as JSON
                                        return JSON.parse(text);
                                    } catch (e) {
                                        // Not valid JSON, log it and throw error
                                        console.error('Invalid JSON in response:', text);
                                        throw new Error('Invalid server response');
                                    }
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Success
                                vocabularyModal.classList.add('hidden');

                                // Clear form
                                vocabularyWord.value = '';
                                vocabularyDefinition.value = '';
                                vocabularyContext.value = '';
                                vocabularyNotes.value = '';
                                document.querySelector('input[name="difficulty"][value="easy"]').checked =
                                    true;

                                // Show success message
                                alert('Word saved to vocabulary successfully!');
                            } else {
                                throw new Error(data.message || 'Error saving word');
                            }
                        })
                        .catch(error => {
                            console.error('Error saving vocabulary:', error);
                            alert('Error saving word: ' + error.message);
                        });
                });

                // Pronunciation functionality for the tooltip
                pronounceTooltipBtn.addEventListener('click', function() {
                    if (!currentSelection) return;

                    const selectedText = currentSelection.toString().trim();
                    if (selectedText.length === 0) return;

                    // Show loading indicator
                    tooltipAudioStatus.classList.remove('hidden');

                    // Try to fetch high-quality audio from Free Dictionary API first
                    fetch(`https://api.dictionaryapi.dev/api/v2/entries/en/${encodeURIComponent(selectedText)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Word not found');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Look for audio URL in the response
                            let audioUrl = null;

                            // Try to find a phonetics entry with audio
                            if (data[0] && data[0].phonetics) {
                                const phoneticsWithAudio = data[0].phonetics.filter(p => p.audio && p.audio
                                    .trim() !== '');

                                if (phoneticsWithAudio.length > 0) {
                                    // Prefer US pronunciation if available
                                    const usAudio = phoneticsWithAudio.find(p => p.audio.includes(
                                        'us.mp3'));
                                    audioUrl = usAudio ? usAudio.audio : phoneticsWithAudio[0].audio;
                                }
                            }

                            if (audioUrl) {
                                // Create and play audio element
                                const audio = new Audio(audioUrl);
                                audio.onloadeddata = function() {
                                    tooltipAudioStatus.classList.add('hidden');
                                };
                                audio.onerror = function() {
                                    tooltipAudioStatus.classList.add('hidden');
                                    // Fallback to Web Speech API
                                    playWebSpeechPronunciation(selectedText);
                                };
                                audio.play();
                            } else {
                                // No audio URL found, use Web Speech API
                                tooltipAudioStatus.classList.add('hidden');
                                playWebSpeechPronunciation(selectedText);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching pronunciation:', error);
                            tooltipAudioStatus.classList.add('hidden');
                            // Fallback to Web Speech API
                            playWebSpeechPronunciation(selectedText);
                        });
                });

                // Function to play pronunciation using Web Speech API
                function playWebSpeechPronunciation(text) {
                    if ('speechSynthesis' in window) {
                        const utterance = new SpeechSynthesisUtterance(text);

                        // Try to get a good voice
                        let voices = speechSynthesis.getVoices();
                        if (voices.length > 0) {
                            // Prefer voices with these names (they tend to sound better)
                            const preferredVoices = ['Google UK English', 'Microsoft Zira', 'Alex', 'Samantha'];
                            for (const name of preferredVoices) {
                                const voice = voices.find(v => v.name.includes(name));
                                if (voice) {
                                    utterance.voice = voice;
                                    break;
                                }
                            }

                            // If no preferred voice found, try to find a good English voice
                            if (!utterance.voice) {
                                const englishVoice = voices.find(v => v.lang.startsWith('en-'));
                                if (englishVoice) {
                                    utterance.voice = englishVoice;
                                }
                            }
                        }

                        // Set properties for better pronunciation
                        utterance.rate = 0.9; // Slightly slower
                        utterance.pitch = 1.0; // Normal pitch

                        // Speak the word
                        speechSynthesis.speak(utterance);
                    } else {
                        // Fallback if speech synthesis isn't available
                        console.log('Speech synthesis not supported');
                        alert('Speech synthesis is not supported in your browser.');
                    }
                }
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
