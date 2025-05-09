<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Vocabulary Statistics') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('vocabulary.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                    Back to Vocabulary
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
            <!-- Overview Stats -->
            <div class="mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Vocabulary Overview</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-blue-500 text-xs uppercase font-semibold mb-1">Total Words</div>
                                <div class="text-3xl font-bold text-blue-700">{{ $stats['total_words'] }}</div>
                            </div>

                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="text-yellow-600 text-xs uppercase font-semibold mb-1">Total Reviews</div>
                                <div class="text-3xl font-bold text-yellow-700">{{ $stats['total_reviews'] }}</div>
                            </div>

                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-green-600 text-xs uppercase font-semibold mb-1">Words Mastered</div>
                                <div class="text-3xl font-bold text-green-700">
                                    {{ $stats['mastery_levels']['mastered'] }}</div>
                                <div class="text-xs text-green-600 mt-1">
                                    {{ $stats['total_words'] > 0 ? round(($stats['mastery_levels']['mastered'] / $stats['total_words']) * 100) : 0 }}%
                                    of all words
                                </div>
                            </div>

                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-red-500 text-xs uppercase font-semibold mb-1">Due for Review</div>
                                <div class="text-3xl font-bold text-red-700">{{ $stats['words_due_for_review'] }}</div>
                                <div class="text-xs text-red-600 mt-1">
                                    {{ $stats['total_words'] > 0 ? round(($stats['words_due_for_review'] / $stats['total_words']) * 100) : 0 }}%
                                    of all words
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mastery Distribution -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Mastery Distribution</h3>

                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">Mastered</span>
                                    <span class="text-sm text-gray-500">{{ $stats['mastery_levels']['mastered'] }}
                                        words</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-green-600 h-2.5 rounded-full"
                                        style="width: {{ $stats['total_words'] > 0 ? ($stats['mastery_levels']['mastered'] / $stats['total_words']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">Confident</span>
                                    <span class="text-sm text-gray-500">{{ $stats['mastery_levels']['confident'] }}
                                        words</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-green-500 h-2.5 rounded-full"
                                        style="width: {{ $stats['total_words'] > 0 ? ($stats['mastery_levels']['confident'] / $stats['total_words']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">Learning</span>
                                    <span class="text-sm text-gray-500">{{ $stats['mastery_levels']['learning'] }}
                                        words</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-yellow-500 h-2.5 rounded-full"
                                        style="width: {{ $stats['total_words'] > 0 ? ($stats['mastery_levels']['learning'] / $stats['total_words']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">Beginner</span>
                                    <span class="text-sm text-gray-500">{{ $stats['mastery_levels']['beginner'] }}
                                        words</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-orange-500 h-2.5 rounded-full"
                                        style="width: {{ $stats['total_words'] > 0 ? ($stats['mastery_levels']['beginner'] / $stats['total_words']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">New</span>
                                    <span class="text-sm text-gray-500">{{ $stats['mastery_levels']['new'] }}
                                        words</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-red-500 h-2.5 rounded-full"
                                        style="width: {{ $stats['total_words'] > 0 ? ($stats['mastery_levels']['new'] / $stats['total_words']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recently Added Words -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Recently Added Words</h3>

                        @if ($stats['recently_added']->isEmpty())
                            <p class="text-gray-500 text-center py-4">No vocabulary words added yet.</p>
                        @else
                            <div class="space-y-3">
                                @foreach ($stats['recently_added'] as $word)
                                    <div class="border rounded-md p-3 hover:bg-gray-50">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <a href="{{ route('vocabulary.show', $word) }}"
                                                    class="font-medium text-blue-600 hover:text-blue-800">
                                                    {{ $word->word }}
                                                </a>
                                                <div class="text-sm text-gray-500">
                                                    Added {{ $word->created_at->diffForHumans() }}
                                                </div>
                                            </div>
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
                                        @if ($word->definition)
                                            <div class="text-sm text-gray-600 mt-1">
                                                {{ Str::limit($word->definition, 100) }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 text-center">
                                <a href="{{ route('vocabulary.index') }}"
                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                    View All Words →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Learning Progress -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Learning Progress</h3>

                    <div class="text-center p-8">
                        @if ($stats['total_words'] > 0)
                            <div class="inline-block relative w-64 h-64">
                                <!-- Circle background -->
                                <svg class="w-full h-full" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb"
                                        stroke-width="10" />

                                    <!-- Mastered words -->
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="#059669"
                                        stroke-width="10" stroke-dasharray="283"
                                        stroke-dashoffset="{{ 283 - 283 * ($stats['mastery_levels']['mastered'] / $stats['total_words']) }}"
                                        transform="rotate(-90 50 50)" />
                                </svg>

                                <!-- Center text -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-3xl font-bold text-green-700">
                                        {{ round(($stats['mastery_levels']['mastered'] / $stats['total_words']) * 100) }}%
                                    </span>
                                    <span class="text-sm text-gray-500">Mastered</span>
                                </div>
                            </div>

                            <p class="mt-4 text-gray-600">
                                You've mastered {{ $stats['mastery_levels']['mastered'] }} out of
                                {{ $stats['total_words'] }} vocabulary words.
                                Keep practicing with flashcards to improve your mastery!
                            </p>
                        @else
                            <div class="text-gray-500">
                                <p>No vocabulary data yet.</p>
                                <p class="mt-2">Add words to your vocabulary while reading to start tracking your
                                    progress.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
