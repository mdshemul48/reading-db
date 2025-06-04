// PDF UI Events Module
// Handles user interactions and event listeners

const UIEvents = (function() {
    function initEventListeners() {
        const state = PDFCore.getState();
        const elements = PDFCore.init();

        // Initialize color manager
        ColorManager.init();

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

        // Color settings panel
        const colorSettingsToggle = document.getElementById('color-settings-toggle');
        const colorSettingsPanel = document.getElementById('color-settings-panel');
        const closeSettingsBtn = document.getElementById('close-settings-btn');

        if (colorSettingsToggle && colorSettingsPanel) {
            // Toggle settings panel
            colorSettingsToggle.addEventListener('click', function() {
                colorSettingsPanel.classList.toggle('visible');

                // Update controls to match current settings when opening
                if (colorSettingsPanel.classList.contains('visible')) {
                    ColorManager.updateUIControls();
                }
            });

            // Close settings panel
            closeSettingsBtn.addEventListener('click', function() {
                colorSettingsPanel.classList.remove('visible');
            });

            // Click outside to close
            document.addEventListener('click', function(e) {
                if (!colorSettingsPanel.contains(e.target) &&
                    e.target !== colorSettingsToggle &&
                    !colorSettingsToggle.contains(e.target)) {
                    colorSettingsPanel.classList.remove('visible');
                }
            });

            // Set up input event handlers for immediate feedback
            const brightnessSlider = document.getElementById('brightness-slider');
            if (brightnessSlider) {
                brightnessSlider.addEventListener('input', function() {
                    const value = parseInt(this.value);
                    ColorManager.setBrightness(value);
                    document.getElementById('brightness-value').textContent = value + '%';
                });
            }

            const contrastSlider = document.getElementById('contrast-slider');
            if (contrastSlider) {
                contrastSlider.addEventListener('input', function() {
                    const value = parseInt(this.value);
                    ColorManager.setContrast(value);
                    document.getElementById('contrast-value').textContent = value + '%';
                });
            }

            const warmthSlider = document.getElementById('warmth-slider');
            if (warmthSlider) {
                warmthSlider.addEventListener('input', function() {
                    const value = parseInt(this.value);
                    ColorManager.setWarmth(value);
                    document.getElementById('warmth-value').textContent = value + '%';
                });
            }

            const presetSelector = document.getElementById('color-preset-selector');
            if (presetSelector) {
                presetSelector.addEventListener('change', function() {
                    ColorManager.setColorPreset(this.value);
                });
            }

            // Set up controls in color settings panel
            ColorManager.setupControlListeners();

            // Reset button
            const resetBtn = document.getElementById('reset-settings-btn');
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    ColorManager.resetToDefaults();
                });
            }

            // Apply button (immediately saves settings and closes panel)
            const applyBtn = document.getElementById('apply-settings-btn');
            if (applyBtn) {
                applyBtn.addEventListener('click', function() {
                    ColorManager.applyColorTheme();
                    colorSettingsPanel.classList.remove('visible');
                });
            }
        }

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
