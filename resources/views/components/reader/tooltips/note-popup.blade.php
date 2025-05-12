<!-- Highlight note popup -->
<div id="highlight-note-popup" class="hidden fixed bg-white rounded-lg shadow-xl z-50 border border-gray-200 max-w-sm">
    <div class="p-3">
        <div class="flex justify-between items-start mb-2">
            <div class="font-medium" id="popup-text-content"></div>
            <button id="close-note-popup" class="text-gray-400 hover:text-gray-600 p-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
