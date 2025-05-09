<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Vocabulary') }}
            </h2>
            <div class="flex space-x-3">
                <button id="add-word-btn"
                    class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700">
                    Add Word
                </button>
                <a href="{{ route('vocabulary.stats') }}"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">
                    View Statistics
                </a>
                <a href="{{ route('vocabulary.flashcards') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                    Practice Flashcards
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter and Sort Controls -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('vocabulary.index') }}" method="GET"
                        class="flex flex-wrap items-end gap-4">
                        <!-- Book Filter -->
                        <div>
                            <label for="book_id" class="block text-sm font-medium text-gray-700 mb-1">Book</label>
                            <select name="book_id" id="book_id"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">All Books</option>
                                @foreach ($books as $book)
                                    <option value="{{ $book->id }}"
                                        {{ request('book_id') == $book->id ? 'selected' : '' }}>
                                        {{ $book->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Difficulty Filter -->
                        <div>
                            <label for="difficulty"
                                class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
                            <select name="difficulty" id="difficulty"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">All Difficulties</option>
                                <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Easy
                                </option>
                                <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Medium
                                </option>
                                <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Hard
                                </option>
                            </select>
                        </div>

                        <!-- Sort By -->
                        <div>
                            <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                            <select name="sort" id="sort"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="created_at"
                                    {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Date Added
                                </option>
                                <option value="word" {{ request('sort') == 'word' ? 'selected' : '' }}>Word</option>
                                <option value="next_review_at"
                                    {{ request('sort') == 'next_review_at' ? 'selected' : '' }}>Next Review</option>
                                <option value="last_reviewed_at"
                                    {{ request('sort') == 'last_reviewed_at' ? 'selected' : '' }}>Last Reviewed
                                </option>
                            </select>
                        </div>

                        <!-- Sort Direction -->
                        <div>
                            <label for="direction"
                                class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
                            <select name="direction" id="direction"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="desc" {{ request('direction', 'desc') == 'desc' ? 'selected' : '' }}>
                                    Descending</option>
                                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Ascending
                                </option>
                            </select>
                        </div>

                        <!-- Apply Filters Button -->
                        <div>
                            <button type="submit"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">
                                Apply Filters
                            </button>
                        </div>

                        <!-- Clear Filters -->
                        <div>
                            <a href="{{ route('vocabulary.index') }}"
                                class="text-gray-600 px-4 py-2 rounded-md text-sm font-medium hover:text-gray-900">
                                Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Vocabulary List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if ($vocabulary->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-gray-500">You haven't added any vocabulary words yet.</p>
                            <p class="text-gray-500 mt-2">While reading a book, select text and click the "Save to
                                Vocabulary" button.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Word
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Definition
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Book & Context
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mastery
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Next Review
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($vocabulary as $word)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $word->word }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $word->difficulty === 'easy'
                                                            ? 'bg-green-100 text-green-800'
                                                            : ($word->difficulty === 'medium'
                                                                ? 'bg-yellow-100 text-yellow-800'
                                                                : 'bg-red-100 text-red-800') }}">
                                                        {{ ucfirst($word->difficulty) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900 max-w-md">
                                                    {{ Str::limit($word->definition, 150) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    @if ($word->book)
                                                        <a href="{{ route('books.reader', $word->book) }}"
                                                            class="text-blue-600 hover:text-blue-900">
                                                            {{ $word->book->title }}
                                                        </a>
                                                        @if ($word->page_number)
                                                            <span class="text-gray-500">(Page
                                                                {{ $word->page_number }})</span>
                                                        @endif
                                                    @else
                                                        <span class="text-gray-500">No book</span>
                                                    @endif
                                                </div>
                                                @if ($word->context)
                                                    <div class="text-xs text-gray-500 mt-1 italic max-w-md">
                                                        "{{ Str::limit($word->context, 100) }}"
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-16 bg-gray-200 rounded-full h-2.5">
                                                        <div class="bg-{{ $word->getMasteryColor() }} h-2.5 rounded-full"
                                                            style="width: {{ $word->getMasteryPercentage() }}%"></div>
                                                    </div>
                                                    <span
                                                        class="ml-2 text-xs text-gray-600">{{ $word->getMasteryPercentage() }}%</span>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">{{ $word->getMasteryLevel() }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    @if ($word->next_review_at)
                                                        {{ $word->next_review_at->format('M d, Y') }}
                                                        <div class="text-xs text-gray-500">
                                                            {{ $word->next_review_at->diffForHumans() }}
                                                        </div>
                                                    @else
                                                        <span class="text-gray-500">Not scheduled</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('vocabulary.show', $word) }}"
                                                    class="text-indigo-600 hover:text-indigo-900 mr-2">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $vocabulary->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Vocabulary Word Modal -->
    <div id="add-word-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Add New Vocabulary Word
                            </h3>
                            <div class="mt-4">
                                <form id="add-word-form" class="space-y-4">
                                    <div>
                                        <label for="word"
                                            class="block text-sm font-medium text-gray-700">Word</label>
                                        <input type="text" name="word" id="word"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required>
                                    </div>

                                    <div>
                                        <label for="definition"
                                            class="block text-sm font-medium text-gray-700">Definition</label>
                                        <textarea name="definition" id="definition" rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                                    </div>

                                    <div>
                                        <label for="context" class="block text-sm font-medium text-gray-700">Context
                                            (Example Sentence)</label>
                                        <textarea name="context" id="context" rows="2"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                                    </div>

                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700">Personal
                                            Notes</label>
                                        <textarea name="notes" id="notes" rows="2"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                                    </div>

                                    <div>
                                        <label for="book_id" class="block text-sm font-medium text-gray-700">Book
                                            (Optional)</label>
                                        <select name="book_id" id="book_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="">No Book</option>
                                            @foreach ($books as $book)
                                                <option value="{{ $book->id }}">{{ $book->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="page_number" class="block text-sm font-medium text-gray-700">Page
                                            Number (Optional)</label>
                                        <input type="number" name="page_number" id="page_number" min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Difficulty Level</label>
                                        <div class="mt-2 flex space-x-6">
                                            <label class="inline-flex items-center">
                                                <input type="radio" class="form-radio" name="difficulty"
                                                    value="easy">
                                                <span class="ml-2 text-green-600">Easy</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" class="form-radio" name="difficulty"
                                                    value="medium" checked>
                                                <span class="ml-2 text-yellow-600">Medium</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" class="form-radio" name="difficulty"
                                                    value="hard">
                                                <span class="ml-2 text-red-600">Hard</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div id="add-word-error" class="text-red-600 hidden"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="save-word-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Word
                    </button>
                    <button type="button" id="cancel-add-word"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add Word Modal
            const addWordModal = document.getElementById('add-word-modal');
            const addWordBtn = document.getElementById('add-word-btn');
            const saveWordBtn = document.getElementById('save-word-btn');
            const cancelAddWordBtn = document.getElementById('cancel-add-word');
            const addWordForm = document.getElementById('add-word-form');
            const addWordError = document.getElementById('add-word-error');

            // Show modal when add word button is clicked
            addWordBtn.addEventListener('click', function() {
                addWordModal.classList.remove('hidden');
                addWordError.classList.add('hidden');
                addWordError.textContent = '';
            });

            // Hide modal when cancel button is clicked
            cancelAddWordBtn.addEventListener('click', function() {
                addWordModal.classList.add('hidden');
                addWordForm.reset();
            });

            // Save word
            saveWordBtn.addEventListener('click', function() {
                // Get form data
                const formData = {
                    word: document.getElementById('word').value.trim(),
                    definition: document.getElementById('definition').value.trim() || null,
                    context: document.getElementById('context').value.trim() || null,
                    notes: document.getElementById('notes').value.trim() || null,
                    book_id: document.getElementById('book_id').value || null,
                    page_number: document.getElementById('page_number').value || null,
                    difficulty: document.querySelector('input[name="difficulty"]:checked').value
                };

                // Validate required fields
                if (!formData.word) {
                    addWordError.textContent = 'Word is required';
                    addWordError.classList.remove('hidden');
                    return;
                }

                // Send data to server
                fetch('{{ route('vocabulary.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Success - reload page to show the new word
                            window.location.reload();
                        } else {
                            // Error
                            addWordError.textContent = data.message || 'Error saving vocabulary word';
                            addWordError.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        addWordError.textContent = 'An error occurred while saving the vocabulary word';
                        addWordError.classList.remove('hidden');
                    });
            });

            // Hide modal when clicking outside
            addWordModal.addEventListener('click', function(e) {
                if (e.target === addWordModal) {
                    addWordModal.classList.add('hidden');
                    addWordForm.reset();
                }
            });
        });
    </script>
</x-app-layout>
