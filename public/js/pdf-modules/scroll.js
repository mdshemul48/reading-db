// PDF Scroll Manager Module
// Handles scrolling behavior and page navigation

const ScrollManager = (function() {
    function handleScroll() {
        const state = PDFCore.getState();
        const elements = PDFCore.init();

        // Show scroll indicator
        elements.scrollIndicator.classList.remove('hidden');

        // Clear previous timeout
        clearTimeout(state.scrollTimeout);

        // Hide indicator after inactivity
        const scrollTimeout = setTimeout(() => {
            elements.scrollIndicator.classList.add('hidden');
        }, 1500);

        PDFCore.setState({ scrollTimeout });

        // Check scroll position
        const scrollPosition = elements.pdfScrollContainer.scrollTop;
        const scrollHeight = elements.pdfScrollContainer.scrollHeight;
        const clientHeight = elements.pdfScrollContainer.clientHeight;

        // Determine scroll direction
        const scrollingDown = scrollPosition > state.pdfScrollPos;
        PDFCore.setState({ pdfScrollPos: scrollPosition });

        // Near bottom - load next page
        if (scrollingDown && !state.isLoadingNewPage && scrollPosition + clientHeight >= scrollHeight - 500) {
            const nextPageToLoad = Math.max(...Array.from(state.renderedPages)) + 1;

            if (nextPageToLoad <= state.pdfDoc.numPages) {
                PDFCore.setState({ isLoadingNewPage: true });
                PageRenderer.renderPage(nextPageToLoad, 'bottom');
            }
        }

        // Near top - load previous page
        if (!scrollingDown && !state.isLoadingNewPage && scrollPosition <= 500) {
            const prevPageToLoad = Math.min(...Array.from(state.renderedPages)) - 1;

            if (prevPageToLoad >= 1) {
                PDFCore.setState({ isLoadingNewPage: true });
                PageRenderer.renderPage(prevPageToLoad, 'top');
            }
        }
    }

    function scrollToPage(num) {
        const state = PDFCore.getState();

        if (num < 1 || num > state.pdfDoc.numPages) return;

        // If page is not rendered yet, render it and surrounding pages
        if (!state.renderedPages.has(num)) {
            const pagesToRender = [num];

            // Also render previous and next page if available
            if (num > 1) pagesToRender.unshift(num - 1);
            if (num < state.pdfDoc.numPages) pagesToRender.push(num + 1);

            pagesToRender.forEach(pageNum => {
                if (!state.renderedPages.has(pageNum)) {
                    PageRenderer.renderPage(pageNum);
                }
            });
        }

        // Find the page element and scroll to it
        const pageElement = document.querySelector(`.pdf-page[data-page-number="${num}"]`);
        if (pageElement) {
            pageElement.scrollIntoView({ behavior: 'smooth' });
        } else {
            // If the page element doesn't exist yet, wait and try again
            setTimeout(() => scrollToPage(num), 100);
        }
    }

    return {
        handleScroll,
        scrollToPage
    };
})();
