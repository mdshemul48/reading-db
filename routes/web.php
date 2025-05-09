<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BookEnrollmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // User Management Routes (Admin only)
    Route::middleware('admin')->group(function () {
        Route::resource('user-management', UserManagementController::class);
    });
    
    // Book routes
    Route::get('/books/library', [BookController::class, 'library'])->name('books.library');
    Route::get('/books/my-books', [BookController::class, 'myBooks'])->name('books.my-books');
    Route::get('/books/{book}/download', [BookController::class, 'download'])->name('books.download');
    Route::resource('books', BookController::class);
    
    // Book enrollment routes
    Route::post('/books/{book}/enroll', [BookEnrollmentController::class, 'enroll'])->name('books.enroll');
    Route::delete('/books/{book}/unenroll', [BookEnrollmentController::class, 'unenroll'])->name('books.unenroll');
});

require __DIR__.'/auth.php';
