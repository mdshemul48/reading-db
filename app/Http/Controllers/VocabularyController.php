<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Vocabulary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class VocabularyController extends Controller
{
    /**
     * Display a listing of the user's vocabulary.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Vocabulary::where('user_id', $user->id)
            ->with('book');

        // Filter by book if requested
        if ($request->has('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        // Filter by difficulty if requested
        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Sort by different fields
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $allowedSortFields = ['word', 'created_at', 'last_reviewed_at', 'next_review_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $vocabulary = $query->orderBy($sortField, $sortDirection)
            ->paginate(20)
            ->withQueryString();

        return view('vocabulary.index', [
            'vocabulary' => $vocabulary,
            'books' => Book::where('user_id', $user->id)->get(),
        ]);
    }

    /**
     * Store a newly created vocabulary item.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            // Debug the incoming request
            \Log::info('Vocabulary store request', [
                'content_type' => $request->header('Content-Type'),
                'is_json' => $request->isJson(),
                'input' => $request->all()
            ]);

            // Validation
            $validated = $request->validate([
                'word' => 'required|string|max:255',
                'definition' => 'nullable|string',
                'context' => 'nullable|string',
                'notes' => 'nullable|string',
                'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
                'book_id' => 'nullable|integer|exists:books,id',
                'page_number' => 'nullable|integer|min:1',
            ]);

            // Explicitly set nullable fields to avoid undefined array key errors
            $validated['definition'] = $validated['definition'] ?? null;
            $validated['context'] = $validated['context'] ?? null;
            $validated['notes'] = $validated['notes'] ?? null;
            $validated['book_id'] = $validated['book_id'] ?? null;
            $validated['page_number'] = $validated['page_number'] ?? null;

            // Make sure the book belongs to the user if provided
            if (!empty($validated['book_id'])) {
                $book = Book::find($validated['book_id']);
                if (!$book || $book->user_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid book specified',
                    ], 403);
                }
            }

            // Add user_id to the validated data
            $validated['user_id'] = $user->id;

            // Set default difficulty if not provided or empty
            if (empty($validated['difficulty'])) {
                $validated['difficulty'] = 'medium';
            }

            // Create vocabulary item and set next review date
            $vocabulary = new Vocabulary($validated);
            $vocabulary->calculateNextReviewDate();
            $vocabulary->save();

            return response()->json([
                'success' => true,
                'vocabulary' => $vocabulary,
            ]);
        } catch (\Exception $e) {
            // Log the error with full details
            \Log::error('Vocabulary save error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error saving vocabulary: ' . $e->getMessage(),
                'debug_info' => app()->environment('production') ? null : [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Display the specified vocabulary item.
     */
    public function show(Vocabulary $vocabulary)
    {
        // Authorize access
        if (! Gate::allows('view', $vocabulary)) {
            abort(403);
        }

        return view('vocabulary.show', [
            'vocabulary' => $vocabulary->load('book'),
        ]);
    }

    /**
     * Update the specified vocabulary item.
     */
    public function update(Request $request, Vocabulary $vocabulary)
    {
        // Authorize access
        if (! Gate::allows('update', $vocabulary)) {
            abort(403);
        }

        $validated = $request->validate([
            'word' => 'sometimes|required|string|max:255',
            'definition' => 'nullable|string',
            'context' => 'nullable|string',
            'notes' => 'nullable|string',
            'difficulty' => ['sometimes', 'required', Rule::in(['easy', 'medium', 'hard'])],
            'book_id' => 'nullable|exists:books,id',
            'page_number' => 'nullable|integer|min:1',
        ]);

        // Make sure the book belongs to the user if provided
        if (!empty($validated['book_id'])) {
            $book = Book::find($validated['book_id']);
            if (!$book || $book->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid book specified',
                ], 403);
            }
        }

        // Check if difficulty has changed
        $difficultyChanged = isset($validated['difficulty']) && $validated['difficulty'] !== $vocabulary->difficulty;

        // Update vocabulary
        $vocabulary->fill($validated);

        // Recalculate next review date if difficulty changed
        if ($difficultyChanged) {
            $vocabulary->calculateNextReviewDate();
        }

        $vocabulary->save();

        return response()->json([
            'success' => true,
            'vocabulary' => $vocabulary,
        ]);
    }

    /**
     * Remove the specified vocabulary item.
     */
    public function destroy(Vocabulary $vocabulary)
    {
        // Authorize access
        if (! Gate::allows('delete', $vocabulary)) {
            abort(403);
        }

        $vocabulary->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Record a review and update difficulty.
     */
    public function review(Request $request, Vocabulary $vocabulary)
    {
        // Authorize access
        if (! Gate::allows('update', $vocabulary)) {
            abort(403);
        }

        $validated = $request->validate([
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
        ]);

        // Update difficulty and review info
        $vocabulary->updateDifficulty($validated['difficulty']);
        $vocabulary->save();

        return response()->json([
            'success' => true,
            'vocabulary' => $vocabulary,
            'next_review' => $vocabulary->next_review_at->format('Y-m-d'),
            'mastery' => [
                'percentage' => $vocabulary->getMasteryPercentage(),
                'level' => $vocabulary->getMasteryLevel(),
                'color' => $vocabulary->getMasteryColor(),
                'easy_count' => $vocabulary->easy_count,
                'medium_count' => $vocabulary->medium_count,
                'hard_count' => $vocabulary->hard_count,
            ]
        ]);
    }

    /**
     * Display flashcards for review.
     */
    public function flashcards()
    {
        $user = Auth::user();

        // Get vocabulary items due for review
        $dueVocabulary = Vocabulary::getDueForReview($user->id);

        // Add mastery information
        $dueVocabulary->each(function ($vocabulary) {
            $vocabulary->mastery_percentage = $vocabulary->getMasteryPercentage();
            $vocabulary->mastery_level = $vocabulary->getMasteryLevel();
            $vocabulary->mastery_color = $vocabulary->getMasteryColor();
        });

        return view('vocabulary.flashcards', [
            'vocabulary' => $dueVocabulary,
        ]);
    }

    /**
     * Get vocabulary statistics for dashboard.
     */
    public function stats()
    {
        $user = Auth::user();

        // Get counts by mastery level
        $masteryLevels = [
            'mastered' => 0,
            'confident' => 0,
            'learning' => 0,
            'beginner' => 0,
            'new' => 0
        ];

        $allVocabulary = Vocabulary::where('user_id', $user->id)->get();

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

        // Get review statistics
        $stats = [
            'total_words' => $allVocabulary->count(),
            'total_reviews' => $allVocabulary->sum('review_count'),
            'mastery_levels' => $masteryLevels,
            'words_due_for_review' => Vocabulary::getDueForReview($user->id)->count(),
            'recently_added' => Vocabulary::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
        ];

        return view('vocabulary.stats', [
            'stats' => $stats
        ]);
    }
}
