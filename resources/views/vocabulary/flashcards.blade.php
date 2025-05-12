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
                        <div class="space-x-4">
                            <a href="{{ route('vocabulary.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Return to Vocabulary
                            </a>
                            <form action="{{ route('vocabulary.mark-due') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Mark All for Review
                                </button>
                            </form>
                        </div>
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
                <div class="flashcard-scene">
                    @foreach ($vocabulary as $index => $word)
                        <div class="flashcard-wrapper" style="display: {{ $index === 0 ? 'block' : 'none' }};">
                            <div class="flashcard" id="flashcard-{{ $index }}" data-index="{{ $index }}"
                                data-id="{{ $word->id }}">
                                <div class="flashcard__face flashcard__face--front">
                                    <div class="card-content">
                                        <h3 class="text-2xl font-bold mb-4">{{ $word->word }}</h3>
                                        <span class="badge">{{ ucfirst($word->difficulty) }}</span>
                                        @if ($word->context)
                                            <div class="context mt-4">"{{ $word->context }}"</div>
                                        @endif
                                        <button class="flip-btn mt-6" onclick="flipCard(this)">Show Definition</button>
                                    </div>
                                </div>
                                <div class="flashcard__face flashcard__face--back">
                                    <div class="card-content">
                                        <h3 class="text-2xl font-bold mb-4">{{ $word->word }}</h3>
                                        <div class="definition mb-6">
                                            <h4 class="font-semibold text-gray-700 mb-2">Definition:</h4>
                                            <p class="text-lg">{{ $word->definition ?? 'No definition provided.' }}</p>
                                        </div>
                                        @if ($word->notes)
                                            <div class="notes mb-6">
                                                <h4 class="font-semibold text-gray-700 mb-2">Notes:</h4>
                                                <p>{{ $word->notes }}</p>
                                            </div>
                                        @endif
                                        <div class="review-buttons mb-6">
                                            <button class="review-btn hard" data-difficulty="hard">Hard</button>
                                            <button class="review-btn medium" data-difficulty="medium">Medium</button>
                                            <button class="review-btn easy" data-difficulty="easy">Easy</button>
                                        </div>
                                        <button class="flip-btn" onclick="flipCard(this)">Show Word</button>
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
        .flashcard-scene {
            perspective: 1000px;
            margin: 0 auto;
            max-width: 600px;
        }

        .flashcard-wrapper {
            padding: 20px;
        }

        .flashcard {
            position: relative;
            width: 100%;
            height: 400px;
            transition: transform 0.8s;
            transform-style: preserve-3d;
            cursor: pointer;
        }

        .flashcard.is-flipped {
            transform: rotateY(180deg);
        }

        .flashcard__face {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            border-radius: 16px;
            background-color: white;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .flashcard__face--front {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .flashcard__face--back {
            transform: rotateY(180deg);
            background-color: #f8fafc;
        }

        .card-content {
            text-align: center;
            padding: 20px;
        }

        .flip-btn {
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .flip-btn:hover {
            background-color: #2563eb;
        }

        .review-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .review-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .review-btn:hover {
            transform: translateY(-2px);
        }

        .review-btn.hard {
            background-color: #ef4444;
            color: white;
        }

        .review-btn.medium {
            background-color: #f59e0b;
            color: white;
        }

        .review-btn.easy {
            background-color: #10b981;
            color: white;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.875rem;
            margin: 10px 0;
            background-color: #e9ecef;
        }

        .context {
            font-style: italic;
            margin: 15px 0;
            color: #6c757d;
        }

        .progress-bar-bg {
            width: 100%;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin: 15px 0;
        }

        .progress-bar {
            height: 100%;
            border-radius: 4px;
            background-color: #4CAF50;
            transition: width 0.3s ease;
        }

        .book-info {
            font-size: 0.875rem;
            color: #6c757d;
            margin: 10px 0;
        }

        .book-info span {
            font-weight: 600;
        }
    </style>

    @push('scripts')
        <script>
            console.log('Flashcard script loaded');

            function flipCard(button) {
                console.log('Flip button clicked');
                const card = button.closest('.flashcard');
                console.log('Card found:', card);
                card.classList.toggle('is-flipped');
                console.log('Card flipped, is-flipped class:', card.classList.contains('is-flipped'));
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Add click handlers for review buttons
                document.querySelectorAll('.review-btn').forEach(button => {
                    button.addEventListener('click', async function(e) {
                        e.preventDefault();
                        const card = this.closest('.flashcard');
                        const cardWrapper = card.closest('.flashcard-wrapper');
                        const wordId = card.dataset.id;
                        const difficulty = this.dataset.difficulty;

                        try {
                            const response = await fetch(`/vocabulary/${wordId}/review`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    difficulty
                                })
                            });

                            if (response.ok) {
                                // Hide current card with fade out
                                cardWrapper.style.opacity = '0';
                                cardWrapper.style.transition = 'opacity 0.3s ease';

                                setTimeout(() => {
                                    cardWrapper.style.display = 'none';

                                    // Show next card if it exists
                                    const nextCard = cardWrapper.nextElementSibling;
                                    if (nextCard) {
                                        nextCard.style.display = 'block';
                                        nextCard.style.opacity = '0';
                                        // Fade in next card
                                        setTimeout(() => {
                                            nextCard.style.opacity = '1';
                                        }, 50);
                                    } else {
                                        // Show completion message
                                        const container = document.querySelector(
                                            '.flashcard-scene');
                                        container.innerHTML = `
                                        <div class="text-center p-8 bg-white rounded-lg shadow-sm">
                                            <h3 class="text-2xl font-bold mb-4">Great job!</h3>
                                            <p class="text-gray-600 mb-6">You've completed all your reviews for now.</p>
                                            <a href="/vocabulary"
                                               class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors">
                                                Back to Vocabulary
                                            </a>
                                        </div>
                                    `;
                                    }
                                }, 300);
                            }
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
