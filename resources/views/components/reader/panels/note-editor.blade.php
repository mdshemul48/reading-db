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
