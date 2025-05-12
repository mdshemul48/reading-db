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
                                <label for="vocabulary-context" class="block text-sm font-medium text-gray-700">Context
                                    (from the book)</label>
                                <textarea name="vocabulary-context" id="vocabulary-context" rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="vocabulary-notes" class="block text-sm font-medium text-gray-700">Personal
                                    Notes</label>
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
                                        <input type="radio" class="form-radio" name="difficulty" value="easy"
                                            checked>
                                        <span class="ml-2 text-green-600">Easy</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio" name="difficulty" value="medium">
                                        <span class="ml-2 text-yellow-600">Medium</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio" name="difficulty" value="hard">
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
