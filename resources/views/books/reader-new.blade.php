<x-app-layout>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Remove the default header to maximize space -->
    <x-slot name="header"></x-slot>

    <!-- Reader Components -->
    <x-reader.header :book="$book" :enrollment="$enrollment" />
    <x-reader.pdf-container :enrollment="$enrollment" :pdfUrl="$pdfUrl" />
    <x-reader.panels.annotation />
    <x-reader.tooltips.annotation />
    <x-reader.tooltips.note-popup />
    <x-reader.panels.note-editor />
    <x-reader.modals.dictionary />
    <x-reader.modals.vocabulary :book="$book" />

    <!-- Styles -->
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
</x-app-layout>
