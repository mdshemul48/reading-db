<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\PdfAnnotation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PdfAnnotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Get all annotations for a specific book
     */
    public function getBookAnnotations(Book $book): JsonResponse
    {
        // Ensure user has access to this book
        if ($book->is_private && $book->user_id !== auth()->id() && !auth()->user()->isEnrolledIn($book)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this book.'
            ], 403);
        }

        // Get all annotations for the book and user
        $annotations = PdfAnnotation::where('book_id', $book->id)
            ->where('user_id', auth()->id())
            ->orderBy('page_number')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'annotations' => $annotations
        ]);
    }

    /**
     * Store a new annotation
     */
    public function store(Request $request, Book $book): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'page_number' => 'required|integer|min:1',
            'text_content' => 'nullable|string',
            'annotation_type' => 'required|string|in:highlight,note,underline',
            'note' => 'nullable|string',
            'position_data' => 'required|json',
            'color' => 'nullable|string',
        ]);

        // Ensure user has access to this book
        if ($book->is_private && $book->user_id !== auth()->id() && !auth()->user()->isEnrolledIn($book)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this book.'
            ], 403);
        }

        // Create the annotation
        $annotation = new PdfAnnotation($validated);
        $annotation->user_id = auth()->id();
        $annotation->book_id = $book->id;
        $annotation->save();

        return response()->json([
            'success' => true,
            'message' => 'Annotation saved successfully',
            'annotation' => $annotation
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update an existing annotation
     */
    public function update(Request $request, PdfAnnotation $annotation): JsonResponse
    {
        // Check if user owns this annotation
        if ($annotation->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not own this annotation.'
            ], 403);
        }

        // Validate request
        $validated = $request->validate([
            'note' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        // Update the annotation
        $annotation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Annotation updated successfully',
            'annotation' => $annotation
        ]);
    }

    /**
     * Delete an annotation
     */
    public function destroy(PdfAnnotation $annotation): JsonResponse
    {
        // Check if user owns this annotation
        if ($annotation->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not own this annotation.'
            ], 403);
        }

        // Delete the annotation
        $annotation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Annotation deleted successfully'
        ]);
    }
}
