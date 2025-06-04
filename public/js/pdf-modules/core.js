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
        zoomIn = document.getElementById('zoom-in');
        zoomOut = document.getElementById('zoom-out');
        fullscreenToggle = document.getElementById('fullscreen-toggle');

        // Create scroll indicator
        scrollIndicator = document.createElement('div');
        scrollIndicator.className = 'scroll-indicator hidden';
        scrollIndicator.innerHTML = '<span id="viewable-page">1</span>';
        document.querySelector('.reader-container').appendChild(scrollIndicator);

        return {
            pdfContainer,
            pdfScrollContainer,
            currentPageEl,
            totalPagesEl,
            progressBar,
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
            if (newState.isLoadingNewPage !== undefined) isLoadingNewPage = newState.isLoadingNewPage;
            if (newState.pdfScrollPos !== undefined) pdfScrollPos = newState.pdfScrollPos;
        }
    };
})();
