<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BookEnrollmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\BookReaderController;
use App\Http\Controllers\PdfAnnotationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

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

    // Notes Management routes
    Route::get('/notes', [PdfAnnotationController::class, 'notesManagement'])->name('notes.management');
    Route::get('/notes/all', [PdfAnnotationController::class, 'getAllNotes'])->name('notes.all');

    // Book enrollment routes
    Route::post('/books/{book}/enroll', [BookEnrollmentController::class, 'enroll'])->name('books.enroll');
    Route::delete('/books/{book}/unenroll', [BookEnrollmentController::class, 'unenroll'])->name('books.unenroll');

    // Vocabulary routes
    Route::middleware('web')->group(function () {
        Route::get('/vocabulary', [VocabularyController::class, 'index'])->name('vocabulary.index');
        Route::post('/vocabulary', [VocabularyController::class, 'store'])->name('vocabulary.store');
        Route::get('/vocabulary/{vocabulary}', [VocabularyController::class, 'show'])->name('vocabulary.show');
        Route::put('/vocabulary/{vocabulary}', [VocabularyController::class, 'update'])->name('vocabulary.update');
        Route::delete('/vocabulary/{vocabulary}', [VocabularyController::class, 'destroy'])->name('vocabulary.destroy');
        Route::post('/vocabulary/{vocabulary}/review', [VocabularyController::class, 'review'])->name('vocabulary.review');
        Route::get('/flashcards', [VocabularyController::class, 'flashcards'])->name('vocabulary.flashcards');
        Route::get('/vocabulary-stats', [VocabularyController::class, 'stats'])->name('vocabulary.stats');
    });
});

require __DIR__.'/auth.php';
