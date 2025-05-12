/**
 * PDF Reader Main JavaScript
 *
 * This file contains the core functionality for the PDF reader.
 *
 * For better maintainability, the JavaScript is being modularized:
 * - main.js: Core initialization and event setup
 * - page-tracker.js: Page change handling and progress tracking
 *
 * This modular approach makes the codebase more maintainable by:
 * 1. Separating concerns into logical units
 * 2. Making it easier to debug specific features
 * 3. Allowing for more focused testing
 * 4. Improving code organization and readability
 *
 * Note: In a future update, this could be further modularized with a build system
 * like Webpack or Vite to create a more robust module system.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements and initialize variables
    initializeElements();

    // Setup event listeners
    setupEventListeners();

    // Load the PDF viewer
    setupPDFViewer();

    // Track reading time
    setupReadingTimeTracking();
});

// Initialize DOM elements and variables
function initializeElements() {
    window.readerState = {
        initialPage: parseInt(document.getElementById('pdf-viewer').getAttribute('data-initial-page')) || 1,
        pdfViewer: document.getElementById('pdf-viewer'),
        annotations: [],
        currentSelection: null,
        currentWord: "",
        currentDefinition: "",
        currentAnnotationId: null,
        selectedHighlightColor: '#ffff00', // Default color
        // UI elements
        annotationPanel: document.getElementById('annotation-panel'),
        annotationTooltip: document.getElementById('annotation-tooltip'),
        dictionaryModal: document.getElementById('dictionary-modal'),
        vocabularyModal: document.getElementById('vocabulary-modal'),
        noteEditor: document.getElementById('note-editor'),
        notePopup: document.getElementById('highlight-note-popup'),
        // Reading time tracking
        readingStartTime: new Date(),
        readingDuration: 0,
        isReading: true
    };
}

// Setup event listeners for user interaction
function setupEventListeners() {
    // Annotation panel toggle
    document.getElementById('toggle-annotations').addEventListener('click', function() {
        window.readerState.annotationPanel.classList.toggle('hidden');
        if (!window.readerState.annotationPanel.classList.contains('hidden')) {
            loadAnnotations();
        }
    });

    // Header toggle
    const readerHeader = document.getElementById('reader-header');
    const toggleHeaderBtn = document.getElementById('toggle-header');
    let headerVisible = true;

    toggleHeaderBtn.addEventListener('click', function() {
        if (headerVisible) {
            readerHeader.style.transform = 'translateY(-100%)';
            toggleHeaderBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            `;
            toggleHeaderBtn.style.position = 'fixed';
            toggleHeaderBtn.style.top = '0.5rem';
            toggleHeaderBtn.style.right = '0.5rem';
            toggleHeaderBtn.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
            toggleHeaderBtn.style.zIndex = '60';
        } else {
            readerHeader.style.transform = 'translateY(0)';
            toggleHeaderBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            `;
            toggleHeaderBtn.style.position = 'static';
            toggleHeaderBtn.style.backgroundColor = 'transparent';
        }
        headerVisible = !headerVisible;
    });

    // Fullscreen toggle
    document.getElementById('fullscreen-toggle').addEventListener('click', function() {
        const pdfContainerWrapper = document.querySelector('.pdf-container-wrapper');

        if (!document.fullscreenElement) {
            if (pdfContainerWrapper.requestFullscreen) {
                pdfContainerWrapper.requestFullscreen();
            } else if (pdfContainerWrapper.webkitRequestFullscreen) {
                pdfContainerWrapper.webkitRequestFullscreen();
            } else if (pdfContainerWrapper.msRequestFullscreen) {
                pdfContainerWrapper.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    });

    // Auto-hide header after 3 seconds of inactivity
    let headerTimeout;

    function resetHeaderTimeout() {
        clearTimeout(headerTimeout);
        if (!headerVisible) {
            readerHeader.style.transform = 'translateY(0)';
            headerVisible = true;
            toggleHeaderBtn.style.position = 'static';
            toggleHeaderBtn.style.backgroundColor = 'transparent';
            toggleHeaderBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            `;
        }

        headerTimeout = setTimeout(() => {
            if (headerVisible) {
                readerHeader.style.transform = 'translateY(-100%)';
                headerVisible = false;
                toggleHeaderBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                `;
                toggleHeaderBtn.style.position = 'fixed';
                toggleHeaderBtn.style.top = '0.5rem';
                toggleHeaderBtn.style.right = '0.5rem';
                toggleHeaderBtn.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
                toggleHeaderBtn.style.zIndex = '60';
            }
        }, 3000);
    }

    document.addEventListener('mousemove', resetHeaderTimeout);
    resetHeaderTimeout();

    // Show header when hovering near top of screen
    document.addEventListener('mousemove', function(e) {
        if (e.clientY < 20 && !headerVisible) {
            readerHeader.style.transform = 'translateY(0)';
            headerVisible = true;
            resetHeaderTimeout();
        }
    });
}

// Setup the PDF viewer with initial page and annotations
function setupPDFViewer() {
    const pdfViewer = window.readerState.pdfViewer;

    // Hook into PDF.js iframe load
    pdfViewer.addEventListener('load', function() {
        console.log('PDF.js viewer loaded');

        // Set initial page
        setInitialPage();

        // Inject text selection handler for annotations
        injectTextSelectionHandler();

        // Load and apply annotations
        setTimeout(() => {
            loadAnnotations();
        }, 1500);
    });

    // Set up message listener to communicate with the PDF.js viewer
    window.addEventListener('message', function(e) {
        // Security check - only accept messages from our PDF.js viewer iframe
        if (e.source !== pdfViewer.contentWindow) return;

        const message = e.data;

        // Handle page change events from PDF.js
        if (message && message.type === 'pagechange') {
            handlePageChange(message);
        } else if (message && message.type === 'textSelection') {
            handleTextSelection(message);
        } else if (message && message.type === 'selectionCleared') {
            window.readerState.annotationTooltip.classList.add('hidden');
            window.readerState.currentSelection = null;
        } else if (message && message.type === 'showNotePopup') {
            handleShowNotePopup(message);
        }
    });
}

// Function to load annotations from the server
function loadAnnotations() {
    fetch('/books/' + currentBookId + '/annotations')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.readerState.annotations = data.annotations;
                renderAnnotations();
                applyAnnotationsToDocument();
            }
        })
        .catch(error => {
            console.error('Error loading annotations:', error);
        });
}

// Setup reading time tracking
function setupReadingTimeTracking() {
    const IDLE_TIMEOUT = 60000; // 1 minute of inactivity is considered idle
    let idleTimer = null;

    // Track user activity to determine if they're actively reading
    function resetIdleTimer() {
        clearTimeout(idleTimer);
        if (!window.readerState.isReading) {
            // User returned from being idle, restart the timer
            window.readerState.readingStartTime = new Date();
            window.readerState.isReading = true;
        }

        // Set new idle timer
        idleTimer = setTimeout(() => {
            // User is idle, calculate duration up to this point
            const now = new Date();
            window.readerState.readingDuration += (now - window.readerState.readingStartTime) / 60000; // convert to minutes
            window.readerState.isReading = false;
        }, IDLE_TIMEOUT);
    }

    // Set up activity tracking
    ['mousemove', 'keydown', 'click', 'scroll'].forEach(eventType => {
        document.addEventListener(eventType, resetIdleTimer);
    });

    // Initialize idle timer
    resetIdleTimer();

    // Save progress when user leaves the page
    window.addEventListener('beforeunload', function() {
        const currentPageEl = document.getElementById('current-page');
        const totalPagesEl = document.getElementById('total-pages');

        if (currentPageEl && totalPagesEl) {
            const currentPage = parseInt(currentPageEl.textContent);
            const totalPages = parseInt(totalPagesEl.textContent);

            if (!isNaN(currentPage) && !isNaN(totalPages)) {
                saveProgress(currentPage, totalPages, null, true);
            }
        }
    });
}
