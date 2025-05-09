<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $vocabulary->word }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('vocabulary.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                    Back to Vocabulary
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold mb-4">{{ $vocabulary->word }}</h3>

                        <div class="flex items-center mb-4">
                            <button id="pronounce-btn"
                                class="mr-4 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z"
                                        clip-rule="evenodd" />
                                </svg>
                                Pronounce
                            </button>
                            <div id="audio-status" class="text-sm text-gray-500 mr-4 hidden">
                                <span class="loading">
                                    <svg class="animate-spin h-4 w-4 text-blue-500 inline mr-1"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Loading audio...
                                </span>
                            </div>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ $vocabulary->difficulty === 'easy'
                                    ? 'bg-green-100 text-green-800'
                                    : ($vocabulary->difficulty === 'medium'
                                        ? 'bg-yellow-100 text-yellow-800'
                                        : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($vocabulary->difficulty) }}
                            </span>

                            <div class="ml-4 flex items-center">
                                <div class="w-24 bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-{{ $vocabulary->getMasteryColor() }} h-2.5 rounded-full"
                                        style="width: {{ $vocabulary->getMasteryPercentage() }}%"></div>
                                </div>
                                <span class="ml-2 text-sm text-gray-600">{{ $vocabulary->getMasteryPercentage() }}% -
                                    {{ $vocabulary->getMasteryLevel() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Word Details -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="col-span-2">
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold mb-2">Definition</h4>
                                <div class="bg-gray-50 p-4 rounded">
                                    {{ $vocabulary->definition ?? 'No definition provided.' }}
                                </div>
                            </div>

                            <div class="mb-6">
                                <h4 class="text-lg font-semibold mb-2">Context</h4>
                                <div class="bg-gray-50 p-4 rounded italic">
                                    "{{ $vocabulary->context ?? 'No context provided.' }}"
                                </div>
                            </div>

                            <div class="mb-6">
                                <h4 class="text-lg font-semibold mb-2">Personal Notes</h4>
                                <div class="bg-gray-50 p-4 rounded">
                                    {{ $vocabulary->notes ?? 'No notes provided.' }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="bg-gray-50 p-4 rounded mb-6">
                                <h4 class="text-lg font-semibold mb-2">Source</h4>
                                @if ($vocabulary->book)
                                    <div class="mb-2">
                                        <span class="font-medium">Book:</span>
                                        <a href="{{ route('books.show', $vocabulary->book) }}"
                                            class="text-blue-600 hover:text-blue-900">
                                            {{ $vocabulary->book->title }}
                                        </a>
                                    </div>
                                    @if ($vocabulary->page_number)
                                        <div class="mb-2">
                                            <span class="font-medium">Page:</span> {{ $vocabulary->page_number }}
                                            <a href="{{ route('books.reader', $vocabulary->book) }}?page={{ $vocabulary->page_number }}"
                                                class="text-sm text-blue-600 hover:text-blue-900 ml-2">
                                                Go to page
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <p class="text-gray-500">No book information</p>
                                @endif
                            </div>

                            <div class="bg-gray-50 p-4 rounded">
                                <h4 class="text-lg font-semibold mb-2">Review Information</h4>
                                <div class="mb-2">
                                    <span class="font-medium">Reviews:</span> {{ $vocabulary->review_count }}
                                </div>
                                <div class="mb-2">
                                    <span class="font-medium">Marked as Easy:</span> {{ $vocabulary->easy_count }}
                                    times
                                </div>
                                <div class="mb-2">
                                    <span class="font-medium">Marked as Medium:</span> {{ $vocabulary->medium_count }}
                                    times
                                </div>
                                <div class="mb-2">
                                    <span class="font-medium">Marked as Hard:</span> {{ $vocabulary->hard_count }}
                                    times
                                </div>
                                <div class="mb-2">
                                    <span class="font-medium">Last Reviewed:</span>
                                    {{ $vocabulary->last_reviewed_at ? $vocabulary->last_reviewed_at->format('M d, Y') : 'Never' }}
                                </div>
                                <div class="mb-2">
                                    <span class="font-medium">Next Review:</span>
                                    {{ $vocabulary->next_review_at ? $vocabulary->next_review_at->format('M d, Y') . ' (' . $vocabulary->next_review_at->diffForHumans() . ')' : 'Not scheduled' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Review Panel -->
                    <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                        <h4 class="text-lg font-semibold mb-4">Quick Review</h4>
                        <p class="mb-4">How well do you know this word?</p>

                        <div class="flex space-x-4">
                            <button id="easy-btn"
                                class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                Easy - I know it
                            </button>
                            <button id="medium-btn"
                                class="px-6 py-3 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400">
                                Medium - I'm learning
                            </button>
                            <button id="hard-btn"
                                class="px-6 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Hard - Still difficult
                            </button>
                        </div>

                        <div id="review-result" class="mt-4 hidden"></div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 flex justify-between">
                        <div>
                            <button id="edit-btn"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Edit Word
                            </button>
                        </div>
                        <div>
                            <button id="delete-btn"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Delete Word
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form Modal -->
    <div id="edit-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="edit-form" method="POST" action="{{ route('vocabulary.update', $vocabulary) }}">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Edit Vocabulary
                        </h3>

                        <div class="mb-4">
                            <label for="word" class="block text-sm font-medium text-gray-700 mb-1">Word</label>
                            <input type="text" name="word" id="word" value="{{ $vocabulary->word }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <div class="mb-4">
                            <label for="definition"
                                class="block text-sm font-medium text-gray-700 mb-1">Definition</label>
                            <textarea name="definition" id="definition" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ $vocabulary->definition }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="context" class="block text-sm font-medium text-gray-700 mb-1">Context</label>
                            <textarea name="context" id="context" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ $vocabulary->context }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Personal
                                Notes</label>
                            <textarea name="notes" id="notes" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ $vocabulary->notes }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty Level</label>
                            <div class="mt-2 flex space-x-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" class="form-radio" name="difficulty" value="easy"
                                        {{ $vocabulary->difficulty === 'easy' ? 'checked' : '' }}>
                                    <span class="ml-2 text-green-600">Easy</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" class="form-radio" name="difficulty" value="medium"
                                        {{ $vocabulary->difficulty === 'medium' ? 'checked' : '' }}>
                                    <span class="ml-2 text-yellow-600">Medium</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" class="form-radio" name="difficulty" value="hard"
                                        {{ $vocabulary->difficulty === 'hard' ? 'checked' : '' }}>
                                    <span class="ml-2 text-red-600">Hard</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save Changes
                        </button>
                        <button type="button" id="cancel-edit"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Delete Vocabulary Word
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete "{{ $vocabulary->word }}"? This action cannot be
                                    undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form id="delete-form" method="POST" action="{{ route('vocabulary.destroy', $vocabulary) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                    </form>
                    <button type="button" id="cancel-delete"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for modals and quick review -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit Modal
            const editModal = document.getElementById('edit-modal');
            const editBtn = document.getElementById('edit-btn');
            const cancelEditBtn = document.getElementById('cancel-edit');

            editBtn.addEventListener('click', function() {
                editModal.classList.remove('hidden');
            });

            cancelEditBtn.addEventListener('click', function() {
                editModal.classList.add('hidden');
            });

            // Delete Modal
            const deleteModal = document.getElementById('delete-modal');
            const deleteBtn = document.getElementById('delete-btn');
            const cancelDeleteBtn = document.getElementById('cancel-delete');

            deleteBtn.addEventListener('click', function() {
                deleteModal.classList.remove('hidden');
            });

            cancelDeleteBtn.addEventListener('click', function() {
                deleteModal.classList.add('hidden');
            });

            // Pronunciation feature
            const pronounceBtn = document.getElementById('pronounce-btn');
            const audioStatus = document.getElementById('audio-status');
            const word = "{{ $vocabulary->word }}"; // Get the word

            // Function to play pronunciation using Web Speech API
            function playWebSpeechPronunciation() {
                if ('speechSynthesis' in window) {
                    const utterance = new SpeechSynthesisUtterance(word);

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

            // Function to fetch high-quality audio from external API
            function fetchExternalPronunciation() {
                audioStatus.classList.remove('hidden');

                // Attempt to get pronunciation from Free Dictionary API
                fetch(`https://api.dictionaryapi.dev/api/v2/entries/en/${encodeURIComponent(word)}`)
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
                                const usAudio = phoneticsWithAudio.find(p => p.audio.includes('us.mp3'));
                                audioUrl = usAudio ? usAudio.audio : phoneticsWithAudio[0].audio;
                            }
                        }

                        if (audioUrl) {
                            // Create and play audio element
                            const audio = new Audio(audioUrl);
                            audio.onloadeddata = function() {
                                audioStatus.classList.add('hidden');
                            };
                            audio.onerror = function() {
                                audioStatus.classList.add('hidden');
                                // Fallback to Web Speech API
                                playWebSpeechPronunciation();
                            };
                            audio.play();
                        } else {
                            // No audio URL found, use Web Speech API
                            audioStatus.classList.add('hidden');
                            playWebSpeechPronunciation();
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching pronunciation:', error);
                        audioStatus.classList.add('hidden');
                        // Fallback to Web Speech API
                        playWebSpeechPronunciation();
                    });
            }

            // Add click event for pronunciation button
            pronounceBtn.addEventListener('click', function() {
                // Try to get high-quality audio first
                fetchExternalPronunciation();
            });

            // Quick Review
            const easyBtn = document.getElementById('easy-btn');
            const mediumBtn = document.getElementById('medium-btn');
            const hardBtn = document.getElementById('hard-btn');
            const reviewResult = document.getElementById('review-result');

            function reviewWord(difficulty) {
                fetch('{{ route('vocabulary.review', $vocabulary) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            difficulty: difficulty
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            reviewResult.classList.remove('hidden');
                            reviewResult.classList.add('p-4', 'bg-green-100', 'text-green-800', 'rounded');
                            reviewResult.innerHTML = `
                            <p>Marked as <strong>${difficulty}</strong>. Next review: ${data.next_review}</p>
                            <p class="mt-2">Mastery level: ${data.mastery.percentage}% (${data.mastery.level})</p>
                            <p class="mt-2">Page will refresh in 3 seconds...</p>
                        `;

                            // Disable buttons
                            easyBtn.disabled = true;
                            mediumBtn.disabled = true;
                            hardBtn.disabled = true;

                            // Reload page after 3 seconds to show updated stats
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        } else {
                            reviewResult.classList.remove('hidden');
                            reviewResult.classList.add('p-4', 'bg-red-100', 'text-red-800', 'rounded');
                            reviewResult.innerHTML = `<p>Error: ${data.message || 'Something went wrong'}</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        reviewResult.classList.remove('hidden');
                        reviewResult.classList.add('p-4', 'bg-red-100', 'text-red-800', 'rounded');
                        reviewResult.innerHTML = '<p>Error: Something went wrong. Please try again.</p>';
                    });
            }

            easyBtn.addEventListener('click', function() {
                reviewWord('easy');
            });

            mediumBtn.addEventListener('click', function() {
                reviewWord('medium');
            });

            hardBtn.addEventListener('click', function() {
                reviewWord('hard');
            });

            // Hide modals when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === editModal) {
                    editModal.classList.add('hidden');
                }
                if (e.target === deleteModal) {
                    deleteModal.classList.add('hidden');
                }
            });
        });
    </script>
</x-app-layout>
