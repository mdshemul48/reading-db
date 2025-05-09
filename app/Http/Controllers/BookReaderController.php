<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BookReaderController extends Controller
{
    /**
     * Show the PDF reader for a book.
     */
    public function reader(Book $book)
    {
        // Check if user can access this book
        if ($book->is_private && $book->user_id !== auth()->id() && !auth()->user()->isEnrolledIn($book)) {
            abort(403, 'You do not have access to this book.');
        }

        // Get enrollment record if user is enrolled
        $enrollment = null;
        if (auth()->user()->isEnrolledIn($book)) {
            $enrollment = BookEnrollment::where('user_id', auth()->id())
                ->where('book_id', $book->id)
                ->first();
        } else if ($book->user_id === auth()->id()) {
            // Auto-enroll the user if they own the book
            $enrollment = BookEnrollment::firstOrCreate([
                'user_id' => auth()->id(),
                'book_id' => $book->id
            ]);
        }
        // No enrollment needed for regular users - they can still read the book

        // Get the PDF file URL - ensure it's a full URL
        $pdfPath = $book->file_path;
        $pdfUrl = url(Storage::url($pdfPath));

        // Get both owned books and enrolled books for the vocabulary modal
        $ownedBooks = Book::where('user_id', auth()->id())->get();
        $enrolledBooks = auth()->user()->enrolledBooks()->get();
        $books = $ownedBooks->merge($enrolledBooks)->unique('id');

        return view('books.reader', compact('book', 'enrollment', 'pdfUrl', 'books'));
    }

    /**
     * Update reading progress via AJAX
     */
    public function updateProgress(Request $request, Book $book)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'current_page' => 'required|integer|min:1',
                'total_pages' => 'required|integer|min:1',
                'scroll_position' => 'nullable|numeric|min:0|max:1',
            ]);

            // Check if user is enrolled
            if (!auth()->user()->isEnrolledIn($book)) {
                // Auto-enroll the user to track their progress
                $enrollment = BookEnrollment::firstOrCreate([
                    'user_id' => auth()->id(),
                    'book_id' => $book->id
                ]);
            } else {
                // Get existing enrollment record
                $enrollment = BookEnrollment::where('user_id', auth()->id())
                    ->where('book_id', $book->id)
                    ->first();
            }

            // Update progress
            $enrollment->updateProgress(
                $validated['current_page'],
                $validated['total_pages'],
                $request->has('scroll_position') ? $validated['scroll_position'] : null
            );

            return response()->json([
                'success' => true,
                'message' => 'Reading progress updated.',
                'progress' => $enrollment->getProgressPercentage()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reading progress.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
