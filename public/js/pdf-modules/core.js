// PDF Reader Core Module
// Handles initialization and core functionality

const PDFCore = (function() {
    // PDF.js worker path configuration
    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    // State variables
    let pdfDoc = null;
    let pageNum = 1;
    let scale = parseFloat(localStorage.getItem('pdf-zoom-level')) || 1.0;
    let isDarkMode = localStorage.getItem('pdf-dark-mode') === 'true';
    let renderedPages = new Set();
    let isScrolling = false;
    let scrollTimeout;
    let currentViewablePageNum = 1;
    let pagesCanvases = {};
    let pagePositions = {};
    let isLoadingNewPage = false;
    let pdfScrollPos = 0;

    // DOM elements
    let pdfContainer;
    let pdfScrollContainer;
    let currentPageEl;
    let totalPagesEl;
    let progressBar;
    let darkModeToggle;
    let zoomIn;
    let zoomOut;
    let fullscreenToggle;
    let scrollIndicator;

    // Initialize the module
    function init() {
        // Get DOM elements
        pdfContainer = document.getElementById('pdf-viewer');
        pdfScrollContainer = document.getElementById('pdf-container');
        currentPageEl = document.getElementById('current-page');
        totalPagesEl = document.getElementById('total-pages');
        progressBar = document.getElementById('progress-bar');
        darkModeToggle = document.getElementById('dark-mode-toggle');
        zoomIn = document.getElementById('zoom-in');
        zoomOut = document.getElementById('zoom-out');
        fullscreenToggle = document.getElementById('fullscreen-toggle');

        // Create scroll indicator
        scrollIndicator = document.createElement('div');
        scrollIndicator.className = 'scroll-indicator hidden';
        scrollIndicator.innerHTML = '<span id="viewable-page">1</span>';
        document.querySelector('.reader-container').appendChild(scrollIndicator);

        // Apply dark mode if enabled
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            darkModeToggle.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            `;
        }

        return {
            pdfContainer,
            pdfScrollContainer,
            currentPageEl,
            totalPagesEl,
            progressBar,
            darkModeToggle,
            zoomIn,
            zoomOut,
            fullscreenToggle,
            scrollIndicator
        };
    }

    // Initialize the PDF Reader with a URL and optional initial page
    function initPdfReader(pdfUrl, initialPage = 1) {
        // Set initial page if provided
        if (initialPage > 1) {
            pageNum = initialPage;
        }

        return PDFLoader.loadPdf(pdfUrl);
    }

    // Public API
    return {
        init,
        initPdfReader,
        getState: function() {
            return {
                pdfDoc,
                pageNum,
                scale,
                isDarkMode,
                renderedPages,
                currentViewablePageNum,
                pagesCanvases,
                pagePositions,
                isLoadingNewPage,
                pdfScrollPos,
                scrollTimeout
            };
        },
        setState: function(newState) {
            if (newState.pdfDoc) pdfDoc = newState.pdfDoc;
            if (newState.currentViewablePageNum) currentViewablePageNum = newState.currentViewablePageNum;
            if (typeof newState.scale !== 'undefined') scale = newState.scale;
            if (typeof newState.isDarkMode !== 'undefined') isDarkMode = newState.isDarkMode;
            if (newState.isLoadingNewPage !== undefined) isLoadingNewPage = newState.isLoadingNewPage;
            if (newState.pdfScrollPos !== undefined) pdfScrollPos = newState.pdfScrollPos;
        }
    };
})();
