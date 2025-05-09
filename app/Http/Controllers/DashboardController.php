<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Vocabulary;
use App\Models\ReadingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with comprehensive stats.
     */
    public function index()
    {
        $user = Auth::user();

        // Books statistics
        $userBooks = Book::where('user_id', $user->id)->get();
        $enrolledBooks = $user->enrolledBooks()->get();
        $allUserBooks = $userBooks->merge($enrolledBooks)->unique('id');

        $booksStats = [
            'uploaded_count' => $userBooks->count(),
            'enrolled_count' => $enrolledBooks->count(),
            'total_books' => $allUserBooks->count(),
            'public_books' => $userBooks->where('is_private', false)->count(),
            'private_books' => $userBooks->where('is_private', true)->count(),
            'reading_progress' => $this->calculateReadingProgress($enrolledBooks),
            'recently_read' => $enrolledBooks->sortByDesc('pivot.last_read_at')->take(5),
            'recently_uploaded' => $userBooks->sortByDesc('created_at')->take(5),
        ];

        // Vocabulary statistics
        $allVocabulary = Vocabulary::where('user_id', $user->id)->get();

        // Get counts by mastery level
        $masteryLevels = [
            'mastered' => 0,
            'confident' => 0,
            'learning' => 0,
            'beginner' => 0,
            'new' => 0
        ];

        foreach ($allVocabulary as $word) {
            $masteryPercentage = $word->getMasteryPercentage();

            if ($masteryPercentage >= 90) {
                $masteryLevels['mastered']++;
            } elseif ($masteryPercentage >= 70) {
                $masteryLevels['confident']++;
            } elseif ($masteryPercentage >= 40) {
                $masteryLevels['learning']++;
            } elseif ($masteryPercentage >= 10) {
                $masteryLevels['beginner']++;
            } else {
                $masteryLevels['new']++;
            }
        }

        // Get vocabulary by book
        $vocabByBook = [];
        $vocabularyBooks = Book::whereIn('id', function($query) use ($user) {
            $query->select('book_id')
                  ->from('vocabularies')
                  ->where('user_id', $user->id)
                  ->whereNotNull('book_id')
                  ->distinct();
        })->get();

        foreach ($vocabularyBooks as $book) {
            $vocabByBook[$book->id] = [
                'title' => $book->title,
                'count' => Vocabulary::where('user_id', $user->id)
                    ->where('book_id', $book->id)
                    ->count()
            ];
        }

        // Calculate difficulty distribution
        $difficultyDistribution = [
            'easy' => $allVocabulary->where('difficulty', 'easy')->count(),
            'medium' => $allVocabulary->where('difficulty', 'medium')->count(),
            'hard' => $allVocabulary->where('difficulty', 'hard')->count(),
        ];

        // Recent activities - aggregate all recent actions
        $recentlyAddedWords = Vocabulary::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentlyReviewedWords = Vocabulary::where('user_id', $user->id)
            ->whereNotNull('last_reviewed_at')
            ->orderBy('last_reviewed_at', 'desc')
            ->take(5)
            ->get();

        // Get weekly vocabulary stats for the last 8 weeks
        $weeklyVocabStats = $this->getWeeklyVocabularyStats($user->id, 8);

        // Get daily reading stats for the last 14 days
        $dailyReadingStats = $this->getDailyReadingStats($user->id, 14);

        // Get activity heatmap data
        $heatmapData = $this->getActivityHeatmapData($user->id);

        // Check if there's real activity data before using it
        $hasActivityData = !empty(array_filter($heatmapData, function($day) {
            return $day['total'] > 0;
        }));

        // Only show actual user data, not sample data
        if (!$hasActivityData) {
            $heatmapData = []; // Empty array instead of sample data
        }

        $vocabularyStats = [
            'total_words' => $allVocabulary->count(),
            'total_reviews' => $allVocabulary->sum('review_count'),
            'mastery_levels' => $masteryLevels,
            'words_due_for_review' => Vocabulary::getDueForReview($user->id)->count(),
            'vocabulary_by_book' => $vocabByBook,
            'difficulty_distribution' => $difficultyDistribution,
            'recently_added' => $recentlyAddedWords,
            'recently_reviewed' => $recentlyReviewedWords,
            'weekly_stats' => $weeklyVocabStats,
        ];

        // Activity metrics over time
        $learningActivity = [
            'daily_reading_stats' => $dailyReadingStats,
            'activity_streak' => $this->calculateActivityStreak($user->id),
            'heatmap_data' => $heatmapData,
        ];

        return view('dashboard', [
            'books_stats' => $booksStats,
            'vocabulary_stats' => $vocabularyStats,
            'learning_activity' => $learningActivity,
        ]);
    }

    /**
     * Calculate reading progress across all enrolled books.
     */
    private function calculateReadingProgress($enrolledBooks)
    {
        $totalBooks = $enrolledBooks->count();
        $booksWithProgress = 0;
        $averageProgress = 0;

        // Books with 100% progress
        $completedBooks = 0;

        foreach ($enrolledBooks as $book) {
            if ($book->pivot->total_pages > 0) {
                $booksWithProgress++;
                $progress = ($book->pivot->current_page / $book->pivot->total_pages) * 100;
                $averageProgress += $progress;

                if ($progress >= 99) {
                    $completedBooks++;
                }
            }
        }

        return [
            'total_enrolled' => $totalBooks,
            'average_progress' => $booksWithProgress > 0 ? round($averageProgress / $booksWithProgress) : 0,
            'completed_books' => $completedBooks,
            'books_with_progress' => $booksWithProgress,
        ];
    }

    /**
     * Get weekly vocabulary stats for the given number of weeks.
     */
    private function getWeeklyVocabularyStats($userId, $weeks)
    {
        $result = [];
        $now = Carbon::now();

        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $newWords = Vocabulary::where('user_id', $userId)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();

            $reviews = Vocabulary::where('user_id', $userId)
                ->whereBetween('last_reviewed_at', [$weekStart, $weekEnd])
                ->count();

            $result[] = [
                'week' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'new_words' => $newWords,
                'reviews' => $reviews,
            ];
        }

        // Reverse to show oldest to newest
        return array_reverse($result);
    }

    /**
     * Get daily reading stats for the given number of days.
     */
    private function getDailyReadingStats($userId, $days)
    {
        $result = [];
        $now = Carbon::now();

        // Get date range
        $startDate = $now->copy()->subDays($days - 1)->startOfDay();
        $endDate = $now->copy()->endOfDay();

        // Get all reading sessions in the date range
        $readingSessions = ReadingSession::where('user_id', $userId)
            ->whereBetween('session_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy(function ($session) {
                return $session->session_date->format('Y-m-d');
            });

        // Get books read in this period
        $booksRead = ReadingSession::where('user_id', $userId)
            ->whereBetween('session_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('book_id')
            ->distinct()
            ->get()
            ->pluck('book_id');

        $bookTitles = Book::whereIn('id', $booksRead)
            ->pluck('title', 'id')
            ->toArray();

        // Create array of all days in the range
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateString = $date->format('Y-m-d');
            $formattedDate = $date->format('M d');

            $daySessions = $readingSessions->get($dateString, collect([]));

            // Calculate total pages read for this day
            $pagesRead = $daySessions->sum('pages_read');

            // Get books read on this day
            $dayBooks = $daySessions->pluck('book_id')->unique();
            $bookNames = [];
            foreach($dayBooks as $bookId) {
                if (isset($bookTitles[$bookId])) {
                    $bookNames[] = $bookTitles[$bookId];
                }
            }

            $result[] = [
                'date' => $formattedDate,
                'date_full' => $dateString,
                'pages_read' => $pagesRead,
                'minutes_read' => $daySessions->sum('duration_minutes'),
                'books_read' => $dayBooks->count(),
                'book_titles' => $bookNames,
            ];
        }

        return $result;
    }

    /**
     * Calculate the user's current activity streak.
     */
    private function calculateActivityStreak($userId)
    {
        $now = Carbon::now();
        $yesterday = $now->copy()->subDay();
        $streak = 0;

        // Check if there was activity today
        $todayActivity = $this->checkForActivity($userId, $now);

        // If no activity today, start checking from yesterday
        $currentDate = $todayActivity ? $now : $yesterday;

        while (true) {
            $hasActivity = $this->checkForActivity($userId, $currentDate);

            if ($hasActivity) {
                $streak++;
                $currentDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Check if there was any activity on the given date.
     */
    private function checkForActivity($userId, $date)
    {
        $dateStart = $date->copy()->startOfDay();
        $dateEnd = $date->copy()->endOfDay();

        // Check for vocabulary additions
        $vocabActivity = Vocabulary::where('user_id', $userId)
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->exists();

        if ($vocabActivity) {
            return true;
        }

        // Check for vocabulary reviews
        $reviewActivity = Vocabulary::where('user_id', $userId)
            ->whereBetween('last_reviewed_at', [$dateStart, $dateEnd])
            ->exists();

        if ($reviewActivity) {
            return true;
        }

        // Check for reading activity
        $readingActivity = DB::table('book_enrollments')
            ->where('user_id', $userId)
            ->whereNotNull('last_read_at')
            ->where('last_read_at', '>=', $dateStart)
            ->where('last_read_at', '<=', $dateEnd)
            ->exists();

        return $readingActivity;
    }

    /**
     * Get activity heatmap data for Github-like contribution graph.
     * Returns data for the last 6 months in a format suitable for a heatmap.
     */
    private function getActivityHeatmapData($userId)
    {
        $result = [];
        $now = Carbon::now();
        $startDate = $now->copy()->subMonths(6)->startOfDay();

        // Get all vocabulary activities
        $vocabularyAdditions = DB::table('vocabularies')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $vocabularyReviews = DB::table('vocabularies')
            ->where('user_id', $userId)
            ->where('last_reviewed_at', '>=', $startDate)
            ->select(DB::raw('DATE(last_reviewed_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Get all reading sessions
        $readingSessions = DB::table('reading_sessions')
            ->where('user_id', $userId)
            ->where('session_date', '>=', $startDate->toDateString())
            ->select('session_date as date', DB::raw('SUM(end_page - start_page) as pages_read'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');


        // Get all dates in the range (last 6 months)
        $dateRange = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $now) {
            $dateRange[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // Fill in the data for each day in the last 6 months
        foreach ($dateRange as $dateString) {
            $currentDate = Carbon::parse($dateString);

            // For week number, use ISO week (1-53)
            $weekNumber = (int) $currentDate->format('W');

            // Get day of week (1=Monday, 7=Sunday in ISO format)
            $dayOfWeek = $currentDate->dayOfWeek;

            // Convert to 0-based (0=Monday, 6=Sunday) for our grid
            $dayOfWeek = ($dayOfWeek === 0) ? 6 : $dayOfWeek - 1;

            // Get activity counts
            $vocabAddCount = isset($vocabularyAdditions[$dateString]) ? $vocabularyAdditions[$dateString]->count : 0;
            $vocabReviewCount = isset($vocabularyReviews[$dateString]) ? $vocabularyReviews[$dateString]->count : 0;
            $pagesRead = isset($readingSessions[$dateString]) ? $readingSessions[$dateString]->pages_read : 0;

            // Calculate reading activity level (each page counts as an activity)
            $readingActivity = min(10, $pagesRead); // Cap at 10 for calculation purposes

            // Calculate total activity level
            $totalActivity = $vocabAddCount + $vocabReviewCount + $readingActivity;

            // Assign activity level (0-4) similar to GitHub's levels
            $activityLevel = 0;
            if ($totalActivity > 0) {
                if ($totalActivity >= 10) {
                    $activityLevel = 4;
                } else if ($totalActivity >= 7) {
                    $activityLevel = 3;
                } else if ($totalActivity >= 4) {
                    $activityLevel = 2;
                } else {
                    $activityLevel = 1;
                }
            }

            $result[] = [
                'date' => $currentDate->format('M d, Y'),
                'day' => $dayOfWeek,
                'week' => $weekNumber,
                'total' => $totalActivity,
                'details' => [
                    'vocab_added' => $vocabAddCount,
                    'vocab_reviewed' => $vocabReviewCount,
                    'pages_read' => $pagesRead
                ],
                'level' => $activityLevel
            ];
        }
        return $result;
    }

    /**
     * Create sample heatmap data for demonstration/testing
     */
    private function createSampleHeatmapData()
    {
        $result = [];
        $now = Carbon::now();
        $startDate = $now->copy()->subMonths(6)->startOfDay();

        $currentDate = $startDate->copy();
        while ($currentDate <= $now) {
            $dateString = $currentDate->format('Y-m-d');
            $weekNumber = (int) $currentDate->format('W');
            $dayOfWeek = $currentDate->dayOfWeek;

            // Convert to 0-based (0=Monday, 6=Sunday) for our grid
            $dayOfWeek = ($dayOfWeek === 0) ? 6 : $dayOfWeek - 1;

            // Add some random activity on some days to make the heatmap interesting
            $randomActivity = 0;
            if (rand(0, 10) < 3) { // 30% chance of activity
                $randomActivity = rand(1, 10);
            }

            // Higher chance of activity on weekdays
            if ($dayOfWeek < 5 && rand(0, 10) < 5) { // 50% chance on weekdays
                $randomActivity = max($randomActivity, rand(1, 10));
            }

            // Higher activity in recent days
            $daysAgo = $now->diffInDays($currentDate);
            if ($daysAgo < 14 && rand(0, 10) < 7) { // 70% chance in last 2 weeks
                $randomActivity = max($randomActivity, rand(1, 10));
            }

            // Assign activity level (0-4) similar to GitHub's levels
            $activityLevel = 0;
            if ($randomActivity > 0) {
                if ($randomActivity >= 10) {
                    $activityLevel = 4;
                } else if ($randomActivity >= 7) {
                    $activityLevel = 3;
                } else if ($randomActivity >= 4) {
                    $activityLevel = 2;
                } else {
                    $activityLevel = 1;
                }
            }

            // Split activity between reading and vocabulary randomly
            $pagesRead = $randomActivity > 0 ? rand(0, $randomActivity) : 0;
            $vocabAdded = $randomActivity > $pagesRead ? rand(0, $randomActivity - $pagesRead) : 0;
            $vocabReviewed = $randomActivity - $pagesRead - $vocabAdded;

            $result[] = [
                'date' => $currentDate->format('M d, Y'),
                'day' => $dayOfWeek,
                'week' => $weekNumber,
                'total' => $randomActivity,
                'details' => [
                    'vocab_added' => $vocabAdded,
                    'vocab_reviewed' => $vocabReviewed,
                    'pages_read' => $pagesRead
                ],
                'level' => $activityLevel
            ];

            $currentDate->addDay();
        }

        return $result;
    }
}
