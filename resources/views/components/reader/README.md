# PDF Reader Component Architecture

This directory contains the modular components for the PDF reader feature.

## Component Structure

The PDF reader has been split into smaller, more maintainable components:

```
reader/
├── header.blade.php               # Top navigation bar
├── pdf-container.blade.php        # PDF viewer container
├── styles.blade.php               # Reader-specific CSS
├── modals/
│   ├── dictionary.blade.php       # Dictionary lookup modal
│   └── vocabulary.blade.php       # Save to vocabulary modal
├── panels/
│   ├── annotation.blade.php       # Side annotation panel
│   └── note-editor.blade.php      # Note editor dialog
├── tooltips/
│   ├── annotation.blade.php       # Text selection tooltip
│   └── note-popup.blade.php       # Annotation popup
└── scripts/
    └── initialization.blade.php   # Basic JS initialization
```

## JavaScript

The reader JavaScript has been moved to:

```
public/js/reader/main.js
```

## Usage

To use the reader in a view, include the necessary components:

```php
<x-reader.header :book="$book" :enrollment="$enrollment" />
<x-reader.pdf-container :enrollment="$enrollment" :pdfUrl="$pdfUrl" />
<x-reader.panels.annotation />
<x-reader.tooltips.annotation />
<x-reader.tooltips.note-popup />
<x-reader.panels.note-editor />
<x-reader.modals.dictionary />
<x-reader.modals.vocabulary :book="$book" />
<x-reader.styles />

@push('scripts')
    <script>
        // Pass PHP variables to JavaScript
        window.readerConfig = {
            bookId: {{ $book->id ?? 'null' }},
            csrfToken: "{{ csrf_token() }}",
            updateProgressUrl: "{{ route('books.update-progress', $book) }}",
            annotationsUrl: "{{ route('books.annotations', $book) }}",
            createAnnotationUrl: "{{ route('books.annotations.store', $book) }}",
            saveVocabularyUrl: "{{ route('vocabulary.store') }}"
        };
    </script>
    <script src="{{ asset('js/reader/main.js') }}"></script>
@endpush
```

See `resources/views/books/reader-new.blade.php` for a complete implementation example. 
