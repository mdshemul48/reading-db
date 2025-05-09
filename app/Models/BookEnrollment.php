<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function updateProgress(int $currentPage, ?int $totalPages = null, ?float $scrollPosition = null): self
    {
        $this->current_page = $currentPage;

        if ($scrollPosition !== null) {
            $this->scroll_position = $scrollPosition;
        }

        if ($totalPages !== null) {
            $this->total_pages = $totalPages;
        }

        $this->last_read_at = now();
        $this->save();

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
