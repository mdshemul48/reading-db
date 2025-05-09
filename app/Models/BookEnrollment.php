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
    public function updateProgress(int $currentPage, ?int $totalPages = null): self
    {
        $this->current_page = $currentPage;

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
            return (int) min(100, max(0, round(($this->current_page / $this->total_pages) * 100)));
        }

        return null;
    }
}
