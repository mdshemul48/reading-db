// PDF Loader Module
// Handles loading PDF documents

const PDFLoader = (function() {
    function loadPdf(pdfUrl) {
        return pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
            const state = PDFCore.getState();
            PDFCore.setState({ pdfDoc: pdf });

            const elements = PDFCore.init();
            elements.totalPagesEl.textContent = pdf.numPages;

            // Set initial progress
            ProgressManager.updateProgress();

            // Initial page loading
            PageRenderer.loadInitialPages();

            return pdf;
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            const elements = PDFCore.init();
            elements.pdfContainer.innerHTML = `
                <div style="padding: 20px; text-align: center; color: var(--text-color, #333);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium">Error loading PDF</h3>
                    <p class="mt-2 text-sm">${error.message}</p>
                </div>
            `;
            throw error;
        });
    }

    return {
        loadPdf
    };
})();
