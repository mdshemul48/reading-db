<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vocabulary extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vocabularies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'word',
        'definition',
        'context',
        'notes',
        'difficulty',
        'page_number',
        'review_count',
        'easy_count',
        'medium_count',
        'hard_count',
        'last_reviewed_at',
        'next_review_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_reviewed_at' => 'datetime',
        'next_review_at' => 'datetime',
    ];

    /**
     * Get the user that owns the vocabulary item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book that the vocabulary item was found in.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Calculate next review date based on difficulty and review count.
     * This implements a simple spaced repetition algorithm.
     */
    public function calculateNextReviewDate()
    {
        $now = now();

        // Base intervals for each difficulty level (in days)
        $intervals = [
            'easy' => [1, 3, 7, 14, 30, 60, 120, 240],
            'medium' => [1, 2, 5, 10, 20, 40, 80, 160],
            'hard' => [1, 1, 3, 5, 10, 20, 40, 80]
        ];

        // Ensure difficulty is valid, default to medium if not
        $difficulty = $this->difficulty;
        if (!$difficulty || !isset($intervals[$difficulty])) {
            $difficulty = 'medium';
            $this->difficulty = 'medium';
        }

        // Ensure review_count is initialized
        if (is_null($this->review_count)) {
            $this->review_count = 0;
        }

        $reviewCount = min($this->review_count, 7); // Cap at the last interval

        $daysToAdd = $intervals[$difficulty][$reviewCount];

        $this->next_review_at = $now->addDays($daysToAdd);
        $this->last_reviewed_at = $now;
        $this->review_count++;

        return $this;
    }

    /**
     * Update the difficulty level, track the count, and recalculate next review date.
     */
    public function updateDifficulty(string $newDifficulty)
    {
        // Increment the appropriate difficulty counter
        if ($newDifficulty === 'easy') {
            $this->easy_count++;
        } elseif ($newDifficulty === 'medium') {
            $this->medium_count++;
        } elseif ($newDifficulty === 'hard') {
            $this->hard_count++;
        }

        $this->difficulty = $newDifficulty;
        return $this->calculateNextReviewDate();
    }

    /**
     * Get mastery level of this vocabulary word (0-100%).
     * A higher percentage indicates better mastery.
     */
    public function getMasteryPercentage()
    {
        // If never reviewed, mastery is 0%
        if ($this->review_count === 0) {
            return 0;
        }

        // Calculate weighted score
        // Easy responses contribute most to mastery score, hard responses detract
        $totalResponses = $this->easy_count + $this->medium_count + $this->hard_count;
        if ($totalResponses === 0) {
            return 0;
        }

        // Weight each difficulty type differently to calculate mastery
        $weightedScore = ($this->easy_count * 100 + $this->medium_count * 50) / $totalResponses;

        // Adjust score based on number of reviews (more reviews = more reliable mastery score)
        $reviewFactor = min(1, $this->review_count / 5); // Max factor of 1 at 5+ reviews

        return min(100, round($weightedScore * $reviewFactor));
    }

    /**
     * Get mastery level text description.
     */
    public function getMasteryLevel()
    {
        $masteryPercentage = $this->getMasteryPercentage();

        if ($masteryPercentage >= 90) {
            return 'Mastered';
        } elseif ($masteryPercentage >= 70) {
            return 'Confident';
        } elseif ($masteryPercentage >= 40) {
            return 'Learning';
        } elseif ($masteryPercentage >= 10) {
            return 'Beginner';
        } else {
            return 'New';
        }
    }

    /**
     * Get color for mastery level visualization.
     */
    public function getMasteryColor()
    {
        $masteryPercentage = $this->getMasteryPercentage();

        if ($masteryPercentage >= 90) {
            return 'green-600'; // Mastered
        } elseif ($masteryPercentage >= 70) {
            return 'green-500'; // Confident
        } elseif ($masteryPercentage >= 40) {
            return 'yellow-500'; // Learning
        } elseif ($masteryPercentage >= 10) {
            return 'orange-500'; // Beginner
        } else {
            return 'red-500'; // New
        }
    }

    /**
     * Get vocabularies due for review.
     */
    public static function getDueForReview($userId)
    {
        return self::where('user_id', $userId)
            ->where(function ($query) {
                $query->where('next_review_at', '<=', now())
                    ->orWhereNull('next_review_at');
            })
            ->orderBy('next_review_at')
            ->get();
    }
}
