<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BookEnrollmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\BookReaderController;
use App\Http\Controllers\PdfAnnotationController;
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
    Route::get('/books/{book}/enrollments', [BookController::class, 'enrollments'])->name('books.enrollments');
    Route::delete('/books/{book}/enrollments/{user}', [BookController::class, 'removeEnrollment'])->name('books.remove-enrollment');
    Route::resource('books', BookController::class);

    // PDF Reader routes
    Route::get('/books/{book}/read', [BookReaderController::class, 'reader'])->name('books.reader');
    Route::post('/books/{book}/progress', [BookReaderController::class, 'updateProgress'])
        ->name('books.update-progress')
        ->middleware('web');

    // PDF Annotation routes
    Route::get('/books/{book}/annotations', [PdfAnnotationController::class, 'getBookAnnotations'])
        ->name('books.annotations')
        ->middleware('web');
    Route::post('/books/{book}/annotations', [PdfAnnotationController::class, 'store'])
        ->name('books.annotations.store')
        ->middleware('web');
    Route::put('/annotations/{annotation}', [PdfAnnotationController::class, 'update'])
        ->name('annotations.update')
        ->middleware('web');
    Route::delete('/annotations/{annotation}', [PdfAnnotationController::class, 'destroy'])
        ->name('annotations.destroy')
        ->middleware('web');

    // Book enrollment routes
    Route::post('/books/{book}/enroll', [BookEnrollmentController::class, 'enroll'])->name('books.enroll');
    Route::delete('/books/{book}/unenroll', [BookEnrollmentController::class, 'unenroll'])->name('books.unenroll');
});

require __DIR__.'/auth.php';
