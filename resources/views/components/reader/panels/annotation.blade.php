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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
