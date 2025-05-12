<script>
    document.addEventListener('DOMContentLoaded', function() {
        const initialPage = {{ $enrollment ? $enrollment->current_page : 1 }};
        const initialScrollPosition =
            {{ $enrollment && $enrollment->scroll_position !== null ? $enrollment->scroll_position : 'null' }};
        const pdfViewer = document.getElementById('pdf-viewer');
        let viewerLoaded = false;
        let currentBookId = {{ $book->id ?? 'null' }};
        let currentSelection = null;
        let allAnnotations = [];
        let currentWord = "";
        let currentDefinition = "";

        // Annotation panel elements
        const annotationPanel = document.getElementById('annotation-panel');
        const toggleAnnotationsBtn = document.getElementById('toggle-annotations');
        const closeAnnotationPanelBtn = document.getElementById('close-annotation-panel');
        const annotationsContainer = document.getElementById('annotations-container');
        const highlightColorSelect = document.getElementById('highlight-color');

        // Annotation tooltip elements
        const annotationTooltip = document.getElementById('annotation-tooltip');
        const highlightBtn = document.getElementById('highlight-btn');
        const noteBtn = document.getElementById('note-btn');
        const searchDefinitionBtn = document.getElementById('search-definition-btn');
        const searchWebBtn = document.getElementById('search-web-btn');
        const saveVocabularyBtn = document.getElementById('save-vocabulary-btn');
        const dictionaryLookupBtn = document.getElementById('dictionary-lookup-btn');
        const pronounceTooltipBtn = document.getElementById('pronounce-tooltip-btn');
        const tooltipAudioStatus = document.getElementById('tooltip-audio-status');
        const colorOptions = document.querySelectorAll('.color-option');
        let selectedHighlightColor = '#ffff00'; // Default color

        // Dictionary modal elements
        const dictionaryModal = document.getElementById('dictionary-modal');
        const dictionaryWord = document.getElementById('dictionary-word');
        const dictionaryLoading = document.getElementById('dictionary-loading');
        const dictionaryContent = document.getElementById('dictionary-content');
        const closeDictionaryModalBtn = document.getElementById('close-dictionary-modal');
        const saveToVocabularyBtn = document.getElementById('save-to-vocabulary-btn');

        // Vocabulary modal elements
        const vocabularyModal = document.getElementById('vocabulary-modal');
        const vocabularyWord = document.getElementById('vocabulary-word');
        const vocabularyDefinition = document.getElementById('vocabulary-definition');
        const vocabularyContext = document.getElementById('vocabulary-context');
        const vocabularyNotes = document.getElementById('vocabulary-notes');
        const saveVocabularyModalBtn = document.getElementById('save-vocabulary');
        const closeVocabularyModalBtn = document.getElementById('close-vocabulary-modal');

        // Note editor elements
        const noteEditor = document.getElementById('note-editor');
        const noteText = document.getElementById('note-text');
        const highlightedText = document.getElementById('highlighted-text');
        const saveNoteBtn = document.getElementById('save-note');
        const cancelNoteBtn = document.getElementById('cancel-note');

        // Note popup elements
        const notePopup = document.getElementById('highlight-note-popup');
        const popupTextContent = document.getElementById('popup-text-content');
        const popupNoteContent = document.getElementById('popup-note-content');
        const closeNotePopupBtn = document.getElementById('close-note-popup');
        const popupEditNoteBtn = document.getElementById('popup-edit-note');
        const popupDeleteNoteBtn = document.getElementById('popup-delete-note');
        let currentAnnotationId = null;
    });
</script>
