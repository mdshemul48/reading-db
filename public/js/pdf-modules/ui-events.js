// PDF UI Events Module
// Handles user interactions and event listeners

const UIEvents = (function() {
    function initEventListeners() {
        const state = PDFCore.getState();
        const elements = PDFCore.init();

        // Scroll handling for lazy loading
        elements.pdfScrollContainer.addEventListener('scroll', ScrollManager.handleScroll);

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                Navigation.prevPage();
            } else if (e.key === 'ArrowRight') {
                Navigation.nextPage();
            }
        });

        // Toggle dark mode
        elements.darkModeToggle.addEventListener('click', function() {
            const state = PDFCore.getState();
            const isDarkMode = !state.isDarkMode;
            PDFCore.setState({ isDarkMode });

            document.body.classList.toggle('dark-mode');

            // Update button icon
            if (isDarkMode) {
                elements.darkModeToggle.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                `;
            } else {
                elements.darkModeToggle.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                `;
            }

            // Save preference
            localStorage.setItem('pdf-dark-mode', isDarkMode);
        });

        // Zoom controls
        elements.zoomIn.addEventListener('click', function() {
            const state = PDFCore.getState();
            const newScale = state.scale + 0.1;
            PDFCore.setState({ scale: newScale });
            PageRenderer.handleZoomChange();
        });

        elements.zoomOut.addEventListener('click', function() {
            const state = PDFCore.getState();
            if (state.scale > 0.2) {
                const newScale = state.scale - 0.1;
                PDFCore.setState({ scale: newScale });
                PageRenderer.handleZoomChange();
            }
        });

        // Fullscreen toggle
        elements.fullscreenToggle.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        });

        // Auto-hide header after inactivity
        let headerTimeout;
        const readerHeader = document.getElementById('reader-header');
        const readerContainer = document.querySelector('.reader-container');

        function resetHeaderTimeout() {
            clearTimeout(headerTimeout);
            readerHeader.style.transform = 'translateY(0)';
            readerHeader.style.opacity = '1';
            readerContainer.classList.remove('header-hidden');

            headerTimeout = setTimeout(() => {
                readerHeader.style.transform = 'translateY(-100%)';
                readerHeader.style.opacity = '0';
                readerContainer.classList.add('header-hidden');
            }, 3000);
        }

        document.addEventListener('mousemove', resetHeaderTimeout);
        resetHeaderTimeout();

        // Show header when mouse is near top of screen
        document.addEventListener('mousemove', function(e) {
            if (e.clientY < 60) {
                readerHeader.style.transform = 'translateY(0)';
                readerHeader.style.opacity = '1';
                readerContainer.classList.remove('header-hidden');
                resetHeaderTimeout();
            }
        });

        // Add swipe navigation for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        function checkSwipeDirection() {
            if (touchEndX < touchStartX - 75) {
                Navigation.nextPage(); // Swipe left
            }
            if (touchEndX > touchStartX + 75) {
                Navigation.prevPage(); // Swipe right
            }
        }

        document.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        });

        document.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            checkSwipeDirection();
        });
    }

    return {
        initEventListeners
    };
})();