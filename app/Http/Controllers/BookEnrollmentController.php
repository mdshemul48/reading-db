<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BookEnrollmentController extends Controller
{
    /**
     * Enroll the user in a book.
     */
    public function enroll(Book $book)
    {
        // Check if book is public or user is the owner
        if ($book->is_private && $book->user_id !== auth()->id()) {
            abort(403, 'You cannot enroll in this private book.');
        }

        // Check if already enrolled
        if (auth()->user()->isEnrolledIn($book)) {
            return redirect()->back()->with('info', 'You are already enrolled in this book.');
        }

        // Enroll the user
        auth()->user()->enrolledBooks()->attach($book->id);

        return redirect()->back()->with('success', 'You have successfully enrolled in this book.');
    }

    /**
     * Unenroll the user from a book.
     */
    public function unenroll(Book $book, Request $request)
    {
        // Check if enrolled
        if (!auth()->user()->isEnrolledIn($book)) {
            return redirect()->back()->with('info', 'You are not enrolled in this book.');
        }

        // Validate the password
        $request->validate([
            'password' => 'required',
        ]);

        // Verify password
        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->back()->with('error', 'The password you entered is incorrect.');
        }

        // Unenroll the user
        auth()->user()->enrolledBooks()->detach($book->id);

        return redirect()->back()->with('success', 'You have successfully unenrolled from this book.');
    }
}
