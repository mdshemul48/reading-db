<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BookEnrollment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'current_page',
        'scroll_position',
        'total_pages',
        'last_read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_read_at' => 'datetime',
        'scroll_position' => 'float',
    ];

    /**
     * Get the user for this enrollment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book for this enrollment
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Update reading progress
     */
    public function updateProgress(int $currentPage, ?int $totalPages = null, ?float $scrollPosition = null, ?int $durationMinutes = null): self
    {
        // Store the old page to track page changes
        $oldPage = $this->current_page ?: 0;

        // Validate current page (should be at least 1)
        $currentPage = max(1, $currentPage);

        // Update current page
        $this->current_page = $currentPage;

        // Update scroll position if provided
        if ($scrollPosition !== null) {
            $this->scroll_position = $scrollPosition;
        }

        // Update total pages if provided
        if ($totalPages !== null) {
            $this->total_pages = $totalPages;

            // Ensure current_page doesn't exceed total_pages
            if ($this->current_page > $this->total_pages) {
                $this->current_page = $this->total_pages;
            }
        }

        // Update last read timestamp
        $this->last_read_at = now();
        $this->save();

        // Only create a reading session if there was an actual page change and not the first time opening
        if ($currentPage != $oldPage && $oldPage > 0) {
            // Get the corresponding page numbers based on which is higher (in case of going backward)
            $startPage = min($oldPage, $currentPage);
            $endPage = max($oldPage, $currentPage);

            // Only create session if actually moving forward (reading new pages)
            if ($currentPage > $oldPage) {
                // Create a reading session record
                ReadingSession::create([
                    'user_id' => $this->user_id,
                    'book_id' => $this->book_id,
                    'start_page' => $oldPage,
                    'end_page' => $currentPage,
                    'duration_minutes' => $durationMinutes ?? 1, // Default to 1 minute if not provided
                    'session_date' => Carbon::today(),
                ]);
            }
        }

        return $this;
    }

    /**
     * Get reading progress percentage
     */
    public function getProgressPercentage(): ?int
    {
        if ($this->total_pages && $this->total_pages > 0) {
            $basePercentage = ($this->current_page - 1) / $this->total_pages;

            // Add partial page progress if available
            if ($this->scroll_position !== null) {
                $pageContribution = 1 / $this->total_pages;
                $partialPageProgress = $pageContribution * $this->scroll_position;
                $basePercentage += $partialPageProgress;
            }

            return (int) min(100, max(0, round($basePercentage * 100)));
        }

        return null;
    }
}
