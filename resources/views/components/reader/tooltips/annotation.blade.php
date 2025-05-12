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
