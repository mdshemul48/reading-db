// PDF Progress Manager Module
// Handles progress tracking and saving

const ProgressManager = (function() {
    function updateProgress() {
        const state = PDFCore.getState();
        const elements = PDFCore.init();

        if (!state.pdfDoc) return;

        const percentage = (state.currentViewablePageNum / state.pdfDoc.numPages) * 100;
        elements.progressBar.style.width = percentage + '%';
    }

    function saveProgress() {
        const state = PDFCore.getState();

        // Store locally for immediate access
        localStorage.setItem('pdf-last-page', state.currentViewablePageNum);

        // Server-side progress saving should be implemented here if needed
        // This is just a placeholder for any AJAX call to save progress
        if (window.saveProgressUrl) {
            const progress = {
                current_page: state.currentViewablePageNum,
                total_pages: state.pdfDoc.numPages
            };

            fetch(window.saveProgressUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(progress)
            }).catch(error => {
                console.error('Error saving progress:', error);
            });
        }
    }

    return {
        updateProgress,
        saveProgress
    };
})();
