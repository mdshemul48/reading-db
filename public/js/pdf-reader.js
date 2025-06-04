// PDF Reader Main File
// This is the main entry point for the PDF reader that loads all modules

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the PDF Reader
    const pdfContainer = document.getElementById('pdf-viewer');

    // Initialize if PDF reader is present on the page
    if (pdfContainer) {
        // Initialize UI events
        UIEvents.initEventListeners();

        // If the PDF URL is globally set, initialize the reader
        if (window.pdfUrl) {
            const initialPage = window.initialPage || 1;
            PDFCore.initPdfReader(window.pdfUrl, initialPage);
        }
    }

    // Expose public API
    window.PdfReader = {
        init: PDFCore.initPdfReader,
        prev: Navigation.prevPage,
        next: Navigation.nextPage,
        goToPage: function(num) {
            const state = PDFCore.getState();
            if (num >= 1 && num <= state.pdfDoc.numPages) {
                ScrollManager.scrollToPage(num);
            }
        },
        setZoom: function(newScale) {
            if (newScale >= 0.2 && newScale <= 3) {
                PDFCore.setState({ scale: newScale });
                // Save zoom level to localStorage
                localStorage.setItem('pdf-zoom-level', newScale.toString());
                PageRenderer.handleZoomChange();
            }
        }
    };
});
