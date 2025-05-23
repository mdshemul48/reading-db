<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class BookController extends Controller
{
    /**
     * Display a listing of the books.
     */
    public function index()
    {
        $publicBooks = Book::public()->latest()->get();

        if (auth()->check()) {
            $userBooks = auth()->user()->uploadedBooks;
            $enrolledBooks = auth()->user()->enrolledBooks;
        } else {
            $userBooks = collect();
            $enrolledBooks = collect();
        }

        return view('books.index', compact('publicBooks', 'userBooks', 'enrolledBooks'));
    }

    /**
     * Show the public library of books.
     */
    public function library()
    {
        $books = Book::public()->latest()->paginate(12);
        return view('books.library', compact('books'));
    }

    /**
     * Show the list of user's enrolled books.
     */
    public function myBooks()
    {
        $enrolledBooks = auth()->user()->enrolledBooks()->latest()->get();
        $uploadedBooks = auth()->user()->uploadedBooks()->latest()->get();

        return view('books.my-books', compact('enrolledBooks', 'uploadedBooks'));
    }

    /**
     * Show the form for creating a new book.
     */
    public function create()
    {
        return view('books.create');
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'pdf_file' => 'required|file|mimes:pdf|max:204800',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
            'is_private' => 'sometimes|boolean',
        ]);

        // Handle PDF file upload
        $pdfPath = $request->file('pdf_file')->store('books', 'public');

        // Handle thumbnail upload if provided
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        // Create book
        $book = Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'description' => $request->description,
            'file_path' => $pdfPath,
            'thumbnail_path' => $thumbnailPath,
            'is_private' => (bool) $request->input('is_private', false),
            'user_id' => auth()->id(),
        ]);

        // Auto-enroll the uploader
        $book->enrolledUsers()->attach(auth()->id());

        return redirect()->route('books.show', $book)
            ->with('success', 'Book uploaded successfully.');
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book)
    {
        // Check if user can view this book
        if ($book->is_private && $book->user_id !== auth()->id() && !auth()->user()->isEnrolledIn($book)) {
            abort(403, 'You do not have access to this book.');
        }

        $isEnrolled = auth()->user()->isEnrolledIn($book);

        // Get enrollment details with reading progress if enrolled
        $enrollment = null;
        if ($isEnrolled) {
            $enrollment = auth()->user()->enrolledBooks()
                ->where('book_id', $book->id)
                ->first()->pivot;

            // Cast last_read_at to Carbon datetime instance if it exists
            if ($enrollment->last_read_at) {
                $enrollment->last_read_at = \Carbon\Carbon::parse($enrollment->last_read_at);
            }
        }

        return view('books.show', compact('book', 'isEnrolled', 'enrollment'));
    }

    /**
     * Download the specified book.
     */
    public function download(Book $book)
    {
        // Check if user can download this book
        if ($book->is_private && $book->user_id !== auth()->id() && !auth()->user()->isEnrolledIn($book)) {
            abort(403, 'You do not have access to this book.');
        }

        return Storage::disk('public')->download($book->file_path, $book->title . '.pdf');
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit(Book $book)
    {
        // Check if user owns this book
        if ($book->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You cannot edit this book.');
        }

        return view('books.edit', compact('book'));
    }

    /**
     * Update the specified book in storage.
     */
    public function update(Request $request, Book $book)
    {
        // Check if user owns this book
        if ($book->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You cannot edit this book.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:204800',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
            'is_private' => 'sometimes|boolean',
        ]);

        $data = [
            'title' => $request->title,
            'author' => $request->author,
            'description' => $request->description,
            'is_private' => (bool) $request->input('is_private', false),
        ];

        // Handle PDF file upload if new file provided
        if ($request->hasFile('pdf_file')) {
            // Delete old file
            Storage::disk('public')->delete($book->file_path);

            // Store new file
            $pdfPath = $request->file('pdf_file')->store('books', 'public');
            $data['file_path'] = $pdfPath;
        }

        // Handle thumbnail upload if new thumbnail provided
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($book->thumbnail_path) {
                Storage::disk('public')->delete($book->thumbnail_path);
            }

            // Store new thumbnail
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $data['thumbnail_path'] = $thumbnailPath;
        }

        $book->update($data);

        return redirect()->route('books.show', $book)
            ->with('success', 'Book updated successfully.');
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Book $book, Request $request)
    {
        // Check if user owns this book
        if ($book->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You cannot delete this book.');
        }

        // Validate the password
        $request->validate([
            'password' => 'required',
        ]);

        // Verify password
        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->back()->with('error', 'The password you entered is incorrect.');
        }

        // Check if other users are enrolled in this book
        $otherEnrollments = $book->enrolledUsers()
            ->where('users.id', '!=', auth()->id())
            ->exists();

        if ($otherEnrollments) {
            return redirect()->route('books.show', $book)
                ->with('error', 'This book cannot be deleted because other users are enrolled in it.');
        }

        // Delete files
        Storage::disk('public')->delete($book->file_path);
        if ($book->thumbnail_path) {
            Storage::disk('public')->delete($book->thumbnail_path);
        }

        // Delete book and enrollments (cascading)
        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully.');
    }

    /**
     * Show enrolled users for the book and allow owner to manage them.
     */
    public function enrollments(Book $book)
    {
        // Check if user owns this book
        if ($book->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You can only manage enrollments for your own books.');
        }

        $enrolledUsers = $book->enrolledUsers()->orderBy('name')->get();

        return view('books.enrollments', compact('book', 'enrolledUsers'));
    }

    /**
     * Remove a user's enrollment (by the book owner).
     */
    public function removeEnrollment(Book $book, User $user)
    {
        // Check if current user is the book owner
        if ($book->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You can only manage enrollments for your own books.');
        }

        // Don't allow removing the owner's enrollment
        if ($user->id === $book->user_id) {
            return redirect()->route('books.enrollments', $book)
                ->with('error', 'You cannot remove your own enrollment as the book owner.');
        }

        // Remove the enrollment
        $book->enrolledUsers()->detach($user->id);

        return redirect()->route('books.enrollments', $book)
            ->with('success', "User {$user->name} has been unenrolled from this book.");
    }
}
