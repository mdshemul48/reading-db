<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Flashcards') }}
            </h2>
            <a href="{{ route('vocabulary.index') }}"
                class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                Back to Vocabulary
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if ($vocabulary->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-gray-500 mb-4">You don't have any vocabulary words due for review.</p>
                        <a href="{{ route('vocabulary.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Return to Vocabulary
                        </a>
                    </div>
                </div>
            @else
                <!-- Flashcard Stats -->
                <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-gray-600">Words to Review:</span>
                                <span class="font-bold text-lg ml-2" id="total-count">{{ $vocabulary->count() }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Progress:</span>
                                <span class="font-bold text-lg ml-2" id="progress-count">0</span>
                                <span class="text-gray-600">/</span>
                                <span class="text-gray-600" id="total-display">{{ $vocabulary->count() }}</span>
                            </div>
                        </div>
                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" id="progress-bar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- Flashcard Container -->
                <div class="flashcard-container">
                    @foreach ($vocabulary as $index => $word)
                        <div class="flashcard {{ $index > 0 ? 'hidden' : '' }}" data-index="{{ $index }}"
                            data-id="{{ $word->id }}">
                            <div
                                class="bg-white overflow-hidden shadow-lg sm:rounded-lg transition-all duration-300 transform perspective-500 flashcard-inner">
                                <!-- Front of Card -->
                                <div
                                    class="flashcard-front p-6 bg-white border-b border-gray-200 flex flex-col justify-between min-h-[400px]">
                                    <div>
                                        <div class="flex justify-between items-start mb-6">
                                            <h3 class="text-3xl font-bold text-gray-900">{{ $word->word }}</h3>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            {{ $word->difficulty === 'easy'
                                                ? 'bg-green-100 text-green-800'
                                                : ($word->difficulty === 'medium'
                                                    ? 'bg-yellow-100 text-yellow-800'
                                                    : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($word->difficulty) }}
                                            </span>
                                        </div>

                                        @if ($word->context)
                                            <div class="bg-gray-50 p-4 rounded-md mb-4 italic">
                                                "{{ $word->context }}"
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mb-4">
                                        <div class="flex items-center mb-2">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                <div class="bg-{{ $word->getMasteryColor() }} h-2.5 rounded-full"
                                                    style="width: {{ $word->getMasteryPercentage() }}%"></div>
                                            </div>
                                            <span
                                                class="text-sm text-gray-600">{{ $word->getMasteryPercentage() }}%</span>
                                        </div>
                                        <p class="text-sm text-gray-500">
                                            @if ($word->book)
                                                From <span class="font-medium">{{ $word->book->title }}</span>
                                                @if ($word->page_number)
                                                    (Page {{ $word->page_number }})
                                                @endif
                                            @endif
                                        </p>
                                    </div>

                                    <button
                                        class="flip-btn w-full py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Show Definition
                                    </button>
                                </div>

                                <!-- Back of Card -->
                                <div
                                    class="flashcard-back p-6 bg-white border-b border-gray-200 flex flex-col justify-between min-h-[400px]">
                                    <div>
                                        <div class="flex justify-between items-start mb-4">
                                            <h3 class="text-3xl font-bold text-gray-900">{{ $word->word }}</h3>
                                            <button
                                                class="flip-btn px-3 py-1 bg-gray-200 text-gray-700 rounded-md text-sm hover:bg-gray-300">
                                                Hide
                                            </button>
                                        </div>

                                        <div class="mb-6">
                                            <h4 class="text-lg font-semibold mb-2">Definition</h4>
                                            <div class="bg-gray-50 p-4 rounded">
                                                {{ $word->definition ?? 'No definition provided.' }}
                                            </div>
                                        </div>

                                        @if ($word->notes)
                                            <div class="mb-6">
                                                <h4 class="text-lg font-semibold mb-2">Notes</h4>
                                                <div class="bg-gray-50 p-4 rounded">
                                                    {{ $word->notes }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <p class="text-center mb-4">How well did you know this word?</p>
                                        <div class="grid grid-cols-3 gap-2">
                                            <button
                                                class="difficulty-btn py-3 bg-red-600 text-white rounded-md hover:bg-red-700"
                                                data-difficulty="hard">
                                                Hard
                                            </button>
                                            <button
                                                class="difficulty-btn py-3 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                                                data-difficulty="medium">
                                                Medium
                                            </button>
                                            <button
                                                class="difficulty-btn py-3 bg-green-600 text-white rounded-md hover:bg-green-700"
                                                data-difficulty="easy">
                                                Easy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Completion Card -->
                <div id="completion-card" class="hidden">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <div class="mb-4">
                                <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Great job!</h3>
                            <p class="text-gray-600 mb-6">You've completed all your flashcards for today.</p>
                            <div class="mb-6">
                                <div class="grid grid-cols-3 gap-4 max-w-md mx-auto text-center">
                                    <div>
                                        <div class="text-xl font-bold text-red-600" id="hard-count">0</div>
                                        <div class="text-gray-500 text-sm">Hard</div>
                                    </div>
                                    <div>
                                        <div class="text-xl font-bold text-yellow-500" id="medium-count">0</div>
                                        <div class="text-gray-500 text-sm">Medium</div>
                                    </div>
                                    <div>
                                        <div class="text-xl font-bold text-green-600" id="easy-count">0</div>
                                        <div class="text-gray-500 text-sm">Easy</div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-center space-x-4">
                                <a href="{{ route('vocabulary.index') }}"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                    Back to Vocabulary
                                </a>
                                <button id="restart-btn"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    Start Over
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .perspective-500 {
            perspective: 1000px;
        }

        .flashcard-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        .flashcard.flipped .flashcard-inner {
            transform: rotateY(180deg);
        }

        .flashcard-front,
        .flashcard-back {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
        }

        .flashcard-front {
            transform: rotateY(0deg);
        }

        .flashcard-back {
            transform: rotateY(180deg);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const flashcards = document.querySelectorAll('.flashcard');
            const totalCount = flashcards.length;
            let currentIndex = 0;
            let completedCount = 0;

            // Stats
            let hardCount = 0;
            let mediumCount = 0;
            let easyCount = 0;

            // Progress elements
            const progressBar = document.getElementById('progress-bar');
            const progressCount = document.getElementById('progress-count');

            // Add event listeners to all flip buttons
            document.querySelectorAll('.flip-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.flashcard');
                    card.classList.toggle('flipped');
                });
            });

            // Add event listeners to difficulty buttons
            document.querySelectorAll('.difficulty-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const difficulty = this.getAttribute('data-difficulty');
                    const card = this.closest('.flashcard');
                    const wordId = card.getAttribute('data-id');

                    // Update stats
                    if (difficulty === 'hard') hardCount++;
                    else if (difficulty === 'medium') mediumCount++;
                    else if (difficulty === 'easy') easyCount++;

                    // Send review to the server
                    fetch(`{{ url('vocabulary') }}/${wordId}/review`, {
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
                            console.log('Review saved:', data);
                        })
                        .catch(error => {
                            console.error('Error saving review:', error);
                        });

                    // Move to next card
                    completedCount++;
                    updateProgress();

                    // Check if we've completed all cards
                    if (currentIndex < totalCount - 1) {
                        // Hide current card
                        card.classList.add('hidden');

                        // Show next card
                        currentIndex++;
                        const nextCard = document.querySelector(
                            `.flashcard[data-index="${currentIndex}"]`);
                        nextCard.classList.remove('hidden');
                    } else {
                        // Show completion card
                        showCompletionCard();
                    }
                });
            });

            // Update progress function
            function updateProgress() {
                const percentage = (completedCount / totalCount) * 100;
                progressBar.style.width = `${percentage}%`;
                progressCount.textContent = completedCount;
            }

            // Show completion card
            function showCompletionCard() {
                // Hide all flashcards
                flashcards.forEach(card => {
                    card.classList.add('hidden');
                });

                // Update stats
                document.getElementById('hard-count').textContent = hardCount;
                document.getElementById('medium-count').textContent = mediumCount;
                document.getElementById('easy-count').textContent = easyCount;

                // Show completion card
                document.getElementById('completion-card').classList.remove('hidden');
            }

            // Restart button
            if (document.getElementById('restart-btn')) {
                document.getElementById('restart-btn').addEventListener('click', function() {
                    // Hide completion card
                    document.getElementById('completion-card').classList.add('hidden');

                    // Reset counters
                    currentIndex = 0;
                    completedCount = 0;

                    // Update progress
                    updateProgress();

                    // Reset all cards to front side and hide all except first
                    flashcards.forEach((card, index) => {
                        card.classList.remove('flipped');
                        if (index === 0) {
                            card.classList.remove('hidden');
                        } else {
                            card.classList.add('hidden');
                        }
                    });
                });
            }
        });
    </script>
</x-app-layout>
