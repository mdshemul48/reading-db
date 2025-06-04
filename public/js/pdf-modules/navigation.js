// PDF Navigation Module
// Handles page navigation functions

const Navigation = (function() {
    function prevPage() {
        const state = PDFCore.getState();

        if (state.currentViewablePageNum <= 1) return;

        const prevPageNum = state.currentViewablePageNum - 1;
        ScrollManager.scrollToPage(prevPageNum);
    }

    function nextPage() {
        const state = PDFCore.getState();

        if (!state.pdfDoc || state.currentViewablePageNum >= state.pdfDoc.numPages) return;

        const nextPageNum = state.currentViewablePageNum + 1;
        ScrollManager.scrollToPage(nextPageNum);
    }

    return {
        prevPage,
        nextPage
    };
})();
