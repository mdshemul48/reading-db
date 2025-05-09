<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <div class="text-sm text-gray-500">
                <span class="font-medium">Current Streak:</span>
                <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">
                    {{ $learning_activity['activity_streak'] }}
                    {{ Str::plural('day', $learning_activity['activity_streak']) }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Key Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Books Stat Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Total Books</div>
                                <div class="text-2xl font-semibold text-gray-800">{{ $books_stats['total_books'] }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $books_stats['uploaded_count'] }} uploaded &middot;
                                    {{ $books_stats['enrolled_count'] }} enrolled
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vocabulary Stat Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Vocabulary Words</div>
                                <div class="text-2xl font-semibold text-gray-800">{{ $vocabulary_stats['total_words'] }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $vocabulary_stats['words_due_for_review'] }} due for review
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reading Progress Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="bg-purple-100 p-3 rounded-full mr-4">
                                <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Reading Progress</div>
                                <div class="text-2xl font-semibold text-gray-800">
                                    {{ $books_stats['reading_progress']['average_progress'] }}%</div>
                                <div class="text-xs text-gray-500">
                                    {{ $books_stats['reading_progress']['completed_books'] }}
                                    {{ Str::plural('book', $books_stats['reading_progress']['completed_books']) }}
                                    completed
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews Stat Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="bg-yellow-100 p-3 rounded-full mr-4">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Vocabulary Reviews</div>
                                <div class="text-2xl font-semibold text-gray-800">
                                    {{ $vocabulary_stats['total_reviews'] }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $vocabulary_stats['mastery_levels']['mastered'] }} words mastered
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Reading Activity -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Daily Reading Activity Chart -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Daily Reading Activity</h3>
                            <div class="w-full h-64">
                                <canvas id="readingActivityChart"></canvas>
                            </div>
                            @if (array_sum(array_column($learning_activity['daily_reading_stats'], 'pages_read')) > 0)
                                <div class="mt-3 text-sm text-gray-600">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <span class="font-medium">Total pages read:</span>
                                            <span>{{ array_sum(array_column($learning_activity['daily_reading_stats'], 'pages_read')) }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium">Reading time:</span>
                                            <span>{{ array_sum(array_column($learning_activity['daily_reading_stats'], 'minutes_read')) }}
                                                minutes</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Activity Heatmap (GitHub-style) -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Activity Heatmap</h3>
                            <div class="activity-heatmap w-full">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <div>{{ \Carbon\Carbon::now()->subMonths(6)->format('M Y') }}</div>
                                    <div>{{ \Carbon\Carbon::now()->format('M Y') }}</div>
                                </div>
                                <div class="flex items-start w-full">
                                    <div class="text-xs text-gray-500 mr-2 mt-1 flex-shrink-0">
                                        <div class="h-4 mb-2">M</div>
                                        <div class="h-4 mb-2">W</div>
                                        <div class="h-4 mb-2">F</div>
                                        <div class="h-4 mb-2">S</div>
                                    </div>
                                    <div id="activity-grid" class="flex flex-grow overflow-x-auto pb-2">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center text-xs text-gray-500 justify-end">
                                    <div class="mr-2">Less</div>
                                    <div class="flex space-x-1">
                                        <div class="w-3 h-3 bg-gray-100 border border-gray-200"></div>
                                        <div class="w-3 h-3 bg-green-100 border border-gray-200"></div>
                                        <div class="w-3 h-3 bg-green-300 border border-gray-200"></div>
                                        <div class="w-3 h-3 bg-green-500 border border-gray-200"></div>
                                        <div class="w-3 h-3 bg-green-700 border border-gray-200"></div>
                                    </div>
                                    <div class="ml-2">More</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vocabulary Progress Chart -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Weekly Vocabulary Progress</h3>
                            <div class="w-full h-64">
                                <canvas id="vocabProgressChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Book Progress -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Reading Progress</h3>
                                <a href="{{ route('books.my-books') }}"
                                    class="text-sm text-blue-600 hover:text-blue-800">View All Books</a>
                            </div>

                            @if ($books_stats['recently_read']->isEmpty())
                                <p class="text-gray-500 text-center py-4">No reading activity yet.</p>
                            @else
                                <div class="space-y-5">
                                    @foreach ($books_stats['recently_read']->take(3) as $book)
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-14 bg-gray-200 rounded flex-shrink-0 mr-4 overflow-hidden">
                                                @if ($book->thumbnail_path)
                                                    <img src="{{ Storage::url($book->thumbnail_path) }}"
                                                        alt="{{ $book->title }}" class="w-full h-full object-cover">
                                                @else
                                                    <div
                                                        class="w-full h-full flex items-center justify-center text-gray-400">
                                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow">
                                                <div class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $book->title }}</div>
                                                <div class="flex items-center justify-between text-xs text-gray-500">
                                                    <span>Page {{ $book->pivot->current_page }} /
                                                        {{ $book->pivot->total_pages }}</span>
                                                    <span>{{ round(($book->pivot->current_page / max(1, $book->pivot->total_pages)) * 100) }}%
                                                        complete</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                                    <div class="bg-blue-600 h-1.5 rounded-full"
                                                        style="width: {{ round(($book->pivot->current_page / max(1, $book->pivot->total_pages)) * 100) }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column - Vocabulary Stats -->
                <div class="space-y-6">
                    <!-- Vocabulary Distribution by Difficulty -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Vocabulary by Difficulty</h3>
                            <div class="h-64">
                                <canvas id="difficultyChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Mastery Level Distribution -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Mastery Distribution</h3>
                            <div class="h-64">
                                <canvas id="masteryChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Books with Most Vocabulary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Books with Most Vocabulary</h3>
                                <a href="{{ route('vocabulary.index') }}"
                                    class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                            </div>

                            @if (count($vocabulary_stats['vocabulary_by_book']) === 0)
                                <p class="text-gray-500 text-center py-4">No vocabulary words added yet.</p>
                            @else
                                <div class="space-y-3">
                                    @foreach (collect($vocabulary_stats['vocabulary_by_book'])->sortByDesc('count')->take(5) as $bookId => $data)
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm font-medium truncate flex-grow">{{ $data['title'] }}
                                            </div>
                                            <div
                                                class="text-sm font-medium bg-blue-100 text-blue-800 py-0.5 px-2 rounded-full">
                                                {{ $data['count'] }} {{ Str::plural('word', $data['count']) }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Words Due for Review -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Due for Review</h3>
                                <a href="{{ route('vocabulary.flashcards') }}"
                                    class="text-sm text-blue-600 hover:text-blue-800">Practice Now</a>
                            </div>

                            @if ($vocabulary_stats['words_due_for_review'] === 0)
                                <p class="text-gray-500 text-center py-4">No words due for review.</p>
                            @else
                                <div class="text-center">
                                    <div class="inline-block relative w-32 h-32">
                                        <!-- Circle progress bar -->
                                        <svg class="w-full h-full" viewBox="0 0 100 100">
                                            <circle cx="50" cy="50" r="45" fill="none"
                                                stroke="#e5e7eb" stroke-width="8" />
                                            <circle cx="50" cy="50" r="45" fill="none"
                                                stroke="#ef4444" stroke-width="8" stroke-dasharray="283"
                                                stroke-dashoffset="{{ 283 - 283 * ($vocabulary_stats['words_due_for_review'] / max(1, $vocabulary_stats['total_words'])) }}"
                                                transform="rotate(-90 50 50)" />
                                        </svg>
                                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                                            <span
                                                class="text-2xl font-bold text-gray-800">{{ $vocabulary_stats['words_due_for_review'] }}</span>
                                            <span class="text-xs text-gray-500">words</span>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-sm text-gray-600">
                                        {{ round(($vocabulary_stats['words_due_for_review'] / max(1, $vocabulary_stats['total_words'])) * 100) }}%
                                        of your vocabulary needs review
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Reading Activity Chart
            const readingActivityCtx = document.getElementById('readingActivityChart').getContext('2d');
            const readingActivityChart = new Chart(readingActivityCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(
                        array_map(function ($item) {
                            return $item['date'];
                        }, $learning_activity['daily_reading_stats']),
                    ) !!},
                    datasets: [{
                        label: 'Pages Read',
                        data: {!! json_encode(
                            array_map(function ($item) {
                                return $item['pages_read'];
                            }, $learning_activity['daily_reading_stats']),
                        ) !!},
                        backgroundColor: 'rgba(99, 102, 241, 0.6)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            precision: 0
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                afterLabel: function(context) {
                                    const data = $learning_activity['daily_reading_stats'][context
                                        .dataIndex
                                    ];
                                    let lines = [];

                                    if (data.minutes_read > 0) {
                                        lines.push(`Reading time: ${data.minutes_read} minutes`);
                                    }

                                    if (data.books_read > 0) {
                                        lines.push(`Books: ${data.books_read}`);

                                        if (data.book_titles && data.book_titles.length > 0) {
                                            data.book_titles.forEach(title => {
                                                lines.push(`• ${title}`);
                                            });
                                        }
                                    }

                                    return lines;
                                }
                            }
                        }
                    }
                }
            });

            // Activity Heatmap
            const activityGrid = document.getElementById('activity-grid');
            const heatmapData = {!! json_encode($learning_activity['heatmap_data']) !!};

            // Clear existing content
            activityGrid.innerHTML = '';

            // Check if we have any activity data
            if (heatmapData.length === 0) {
                const emptyMessage = document.createElement('div');
                emptyMessage.className = 'text-center text-gray-500 py-4 w-full';
                emptyMessage.innerText = 'No activity data available yet. Start reading to see your activity!';
                activityGrid.appendChild(emptyMessage);
            } else {
                // Group by week
                const weekMap = {};

                // First, organize data by week number
                heatmapData.forEach(day => {
                    if (!weekMap[day.week]) {
                        weekMap[day.week] = [];
                    }
                    weekMap[day.week].push(day);
                });

                // Convert to a sorted array of week numbers
                const weekNumbers = Object.keys(weekMap).map(Number).sort((a, b) => a - b);

                // Calculate how many weeks to show based on container width
                const containerWidth = activityGrid.clientWidth;
                const cellWidth = 16; // account for cell width + gap
                const maxWeeks = Math.min(26, Math.floor(containerWidth / cellWidth)); // Show max 6 months

                // Check if we have actual data with activity
                const hasActivityWeeks = weekNumbers.some(weekNum => {
                    return weekMap[weekNum].some(day => day.total > 0);
                });

                // If no real activity, show message
                if (!hasActivityWeeks) {
                    const emptyMessage = document.createElement('div');
                    emptyMessage.className = 'text-center text-gray-500 py-4 w-full';
                    emptyMessage.innerText = 'No activity data available yet. Start reading to see your activity!';
                    activityGrid.appendChild(emptyMessage);
                    return;
                }

                // Find the first week with activity
                const firstActiveWeekIndex = weekNumbers.findIndex(weekNum => {
                    return weekMap[weekNum].some(day => day.total > 0);
                });

                // Only show weeks with activity or those after the first activity
                let activeWeeks = weekNumbers;
                if (firstActiveWeekIndex >= 0) {
                    activeWeeks = weekNumbers.slice(Math.max(0, firstActiveWeekIndex - 1));
                }

                // Limit to most recent weeks based on available space and actual data
                const weeksToShow = activeWeeks.slice(-maxWeeks);

                // For each week with actual data, create a column
                weeksToShow.forEach(weekNum => {
                    const weekData = weekMap[weekNum] || [];

                    // Create week container (vertical column of squares)
                    const weekContainer = document.createElement('div');
                    weekContainer.className = 'week-column';

                    // Generate a full week (7 days)
                    // In this grid, each cell represents a day of the week (0 = Monday, 6 = Sunday)
                    for (let i = 0; i < 7; i++) {
                        // Find the day data for this position
                        const dayData = weekData.find(d => d.day === i);

                        // Create the day cell
                        const dayCell = document.createElement('div');
                        dayCell.className = 'w-3 h-3 rounded-sm';

                        if (dayData && dayData.total > 0) {
                            // Set color based on activity level
                            switch (dayData.level) {
                                case 0:
                                    dayCell.className += ' bg-gray-100 border border-gray-200';
                                    break;
                                case 1:
                                    dayCell.className += ' bg-green-100 border border-gray-200';
                                    break;
                                case 2:
                                    dayCell.className += ' bg-green-300 border border-gray-200';
                                    break;
                                case 3:
                                    dayCell.className += ' bg-green-500 border border-gray-200';
                                    break;
                                case 4:
                                    dayCell.className += ' bg-green-700 border border-gray-200';
                                    break;
                                default:
                                    dayCell.className += ' bg-gray-100 border border-gray-200';
                                    break;
                            }

                            // Add tooltip with data
                            const tooltip = document.createElement('div');
                            tooltip.className =
                                'tooltip opacity-0 absolute bg-gray-800 text-white text-xs rounded px-2 py-1 pointer-events-none transition-opacity';
                            tooltip.innerHTML = `
                                <div class="font-bold">${dayData.date}</div>
                                <div>${dayData.total} activities</div>
                                ${dayData.details && dayData.details.pages_read > 0 ? `<div>${dayData.details.pages_read} pages read</div>` : ''}
                                ${dayData.details && dayData.details.vocab_added > 0 ? `<div>${dayData.details.vocab_added} words added</div>` : ''}
                                ${dayData.details && dayData.details.vocab_reviewed > 0 ? `<div>${dayData.details.vocab_reviewed} words reviewed</div>` : ''}
                            `;

                            dayCell.appendChild(tooltip);
                            dayCell.classList.add('relative', 'hover:cursor-pointer');

                            // Toggle tooltip on hover with a small delay for better user experience
                            dayCell.addEventListener('mouseenter', () => {
                                // Ensure all other tooltips are hidden first
                                document.querySelectorAll('.tooltip').forEach(t => {
                                    if (t !== tooltip) t.classList.add('opacity-0');
                                });

                                setTimeout(() => {
                                    tooltip.classList.remove('opacity-0');
                                    tooltip.classList.add('opacity-100');
                                }, 50);
                            });

                            dayCell.addEventListener('mouseleave', () => {
                                setTimeout(() => {
                                    tooltip.classList.add('opacity-0');
                                    tooltip.classList.remove('opacity-100');
                                }, 100);
                            });
                        } else {
                            // Empty day
                            dayCell.className += ' bg-gray-50 border border-gray-100';
                        }

                        weekContainer.appendChild(dayCell);
                    }

                    activityGrid.appendChild(weekContainer);
                });

                // Space out the columns if we have fewer than what fits
                if (weeksToShow.length < maxWeeks / 2) {
                    activityGrid.classList.add('justify-evenly');
                }
            }

            // Vocabulary Progress Chart
            const vocabProgressCtx = document.getElementById('vocabProgressChart').getContext('2d');
            const vocabProgressChart = new Chart(vocabProgressCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(
                        array_map(function ($item) {
                            return $item['week'];
                        }, $vocabulary_stats['weekly_stats']),
                    ) !!},
                    datasets: [{
                            label: 'New Words Added',
                            data: {!! json_encode(
                                array_map(function ($item) {
                                    return $item['new_words'];
                                }, $vocabulary_stats['weekly_stats']),
                            ) !!},
                            borderColor: 'rgba(16, 185, 129, 1)',
                            backgroundColor: 'rgba(16, 185, 129, 0.2)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Word Reviews',
                            data: {!! json_encode(
                                array_map(function ($item) {
                                    return $item['reviews'];
                                }, $vocabulary_stats['weekly_stats']),
                            ) !!},
                            borderColor: 'rgba(245, 158, 11, 1)',
                            backgroundColor: 'rgba(245, 158, 11, 0.2)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }
                }
            });

            // Difficulty Distribution Chart
            const difficultyCtx = document.getElementById('difficultyChart').getContext('2d');
            const difficultyChart = new Chart(difficultyCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Easy', 'Medium', 'Hard'],
                    datasets: [{
                        data: [
                            {{ $vocabulary_stats['difficulty_distribution']['easy'] }},
                            {{ $vocabulary_stats['difficulty_distribution']['medium'] }},
                            {{ $vocabulary_stats['difficulty_distribution']['hard'] }}
                        ],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.6)', // Green
                            'rgba(245, 158, 11, 0.6)', // Yellow/orange
                            'rgba(220, 38, 38, 0.6)' // Red
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(220, 38, 38, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Mastery Level Chart
            const masteryCtx = document.getElementById('masteryChart').getContext('2d');
            const masteryChart = new Chart(masteryCtx, {
                type: 'polarArea',
                data: {
                    labels: ['Mastered', 'Confident', 'Learning', 'Beginner', 'New'],
                    datasets: [{
                        data: [
                            {{ $vocabulary_stats['mastery_levels']['mastered'] }},
                            {{ $vocabulary_stats['mastery_levels']['confident'] }},
                            {{ $vocabulary_stats['mastery_levels']['learning'] }},
                            {{ $vocabulary_stats['mastery_levels']['beginner'] }},
                            {{ $vocabulary_stats['mastery_levels']['new'] }}
                        ],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.6)', // Green
                            'rgba(59, 130, 246, 0.6)', // Blue
                            'rgba(245, 158, 11, 0.6)', // Yellow/orange
                            'rgba(249, 115, 22, 0.6)', // Orange
                            'rgba(220, 38, 38, 0.6)' // Red
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
