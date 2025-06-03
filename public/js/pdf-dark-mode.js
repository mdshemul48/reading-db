/**
 * PDF Dark Mode Injector
 * This script injects dark mode styles into the PDF.js viewer iframe
 * and handles dark mode toggling via UI buttons
 */

// Global function to toggle brightness controls from parent window
window.togglePdfBrightnessControls = function() {
    const container = document.getElementById('brightness-control-container');
    if (container) {
        container.classList.toggle('visible');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Find the PDF viewer iframe
    const pdfViewer = document.getElementById('pdf-viewer');

    if (!pdfViewer) {
        console.error('PDF viewer iframe not found');
        return;
    }

    // Initialize dark mode state from localStorage or default to true (dark mode on by default)
    let isDarkMode = localStorage.getItem('pdf-dark-mode') === null
        ? true  // Default to dark mode if no preference is stored
        : localStorage.getItem('pdf-dark-mode') === 'true';

    // Initialize brightness level from localStorage or default to 0.5 (50%)
    let brightnessLevel = localStorage.getItem('pdf-brightness') || 0.5;
    let contrastLevel = localStorage.getItem('pdf-contrast') || 1.2;
    let sepiaLevel = localStorage.getItem('pdf-sepia') || 0.3;

    // Always save the initial values to localStorage
    localStorage.setItem('pdf-dark-mode', isDarkMode);
    localStorage.setItem('pdf-brightness', brightnessLevel);
    localStorage.setItem('pdf-contrast', contrastLevel);
    localStorage.setItem('pdf-sepia', sepiaLevel);

    // Set up listener for the header dark mode toggle
    const headerDarkModeToggle = document.getElementById('dark-mode-toggle');

    // Set up listener for the brightness settings button
    const brightnessSettingsButton = document.getElementById('brightness-settings-button');

    // Create brightness control container and add it to the page
    createBrightnessControls();

    // Listen for messages from parent window
    window.addEventListener('message', function(event) {
        // Only accept messages from our parent window
        if (event.source !== window.parent) return;

        if (event.data && event.data.type) {
            switch (event.data.type) {
                case 'toggleBrightnessControls':
                    const brightnessContainer = document.getElementById('brightness-control-container');
                    if (brightnessContainer) {
                        brightnessContainer.classList.toggle('visible');
                    }
                    break;
                case 'updateBrightness':
                    if (event.data.value) {
                        brightnessLevel = event.data.value;

                        // Update UI if it exists
                        const brightnessValue = document.getElementById('brightness-value');
                        const brightnessSlider = document.getElementById('brightness-slider');

                        if (brightnessValue) {
                            brightnessValue.textContent = Math.round(brightnessLevel * 100) + '%';
                        }

                        if (brightnessSlider) {
                            brightnessSlider.value = brightnessLevel;
                        }

                        // Save to localStorage
                        localStorage.setItem('pdf-brightness', brightnessLevel);

                        // Update filters
                        updateFilters();
                    }
                    break;
                case 'updateContrast':
                    if (event.data.value) {
                        contrastLevel = event.data.value;

                        // Update UI if it exists
                        const contrastValue = document.getElementById('contrast-value');
                        const contrastSlider = document.getElementById('contrast-slider');

                        if (contrastValue) {
                            contrastValue.textContent = Math.round(contrastLevel * 100) + '%';
                        }

                        if (contrastSlider) {
                            contrastSlider.value = contrastLevel;
                        }

                        // Save to localStorage
                        localStorage.setItem('pdf-contrast', contrastLevel);

                        // Update filters
                        updateFilters();
                    }
                    break;
                case 'updateSepia':
                    if (event.data.value) {
                        sepiaLevel = event.data.value;

                        // Update UI if it exists
                        const sepiaValue = document.getElementById('sepia-value');
                        const sepiaSlider = document.getElementById('sepia-slider');

                        if (sepiaValue) {
                            sepiaValue.textContent = Math.round(sepiaLevel * 100) + '%';
                        }

                        if (sepiaSlider) {
                            sepiaSlider.value = sepiaLevel;
                        }

                        // Save to localStorage
                        localStorage.setItem('pdf-sepia', sepiaLevel);

                        // Update filters
                        updateFilters();
                    }
                    break;
            }
        }
    });

    // Add event listener to the brightness settings button
    if (brightnessSettingsButton) {
        brightnessSettingsButton.addEventListener('click', function() {
            const brightnessContainer = document.getElementById('brightness-control-container');
            if (brightnessContainer) {
                brightnessContainer.classList.toggle('visible');
            }
        });
    }

    if (headerDarkModeToggle) {
        // Update toggle button appearance based on current state
        const updateToggleButton = () => {
            if (isDarkMode) {
                headerDarkModeToggle.classList.add('text-yellow-400');
                headerDarkModeToggle.classList.remove('text-gray-600');
                headerDarkModeToggle.setAttribute('title', 'Switch to Light Mode');
                // Change icon to sun
                headerDarkModeToggle.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                `;
            } else {
                headerDarkModeToggle.classList.add('text-gray-600');
                headerDarkModeToggle.classList.remove('text-yellow-400');
                headerDarkModeToggle.setAttribute('title', 'Switch to Dark Mode');
                // Change icon to moon
                headerDarkModeToggle.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                `;
            }
        };

        // Initialize toggle button
        updateToggleButton();

        // Add click handler to toggle button
        headerDarkModeToggle.addEventListener('click', function() {
            isDarkMode = !isDarkMode;
            localStorage.setItem('pdf-dark-mode', isDarkMode);
            updateToggleButton();
            setupDarkMode();

            // Toggle brightness controls visibility when turning dark mode on/off
            const brightnessContainer = document.getElementById('brightness-control-container');
            if (brightnessContainer) {
                if (isDarkMode) {
                    brightnessContainer.classList.add('visible');
                } else {
                    brightnessContainer.classList.remove('visible');
                }
            }
        });

        // Hide display settings panel when clicking outside of it
        document.addEventListener('click', function(event) {
            const brightnessContainer = document.getElementById('brightness-control-container');
            const brightnessButton = document.getElementById('brightness-settings-button');

            if (brightnessContainer && brightnessContainer.classList.contains('visible')) {
                // Check if click is outside the container and not on the brightness button
                if (!brightnessContainer.contains(event.target) &&
                    (!brightnessButton || !brightnessButton.contains(event.target))) {
                    brightnessContainer.classList.remove('visible');
                }
            }
        });
    }

    // Function to create brightness controls
    function createBrightnessControls() {
        // Create container for brightness controls
        const container = document.createElement('div');
        container.id = 'brightness-control-container';
        container.className = 'brightness-control-container';
        if (isDarkMode) {
            container.classList.add('visible');
        }

        // Create brightness slider
        const brightnessLabel = document.createElement('div');
        brightnessLabel.className = 'slider-label';
        brightnessLabel.innerHTML = `
            <span>Brightness</span>
            <span class="slider-value" id="brightness-value">${Math.round(brightnessLevel * 100)}%</span>
        `;

        const brightnessSlider = document.createElement('input');
        brightnessSlider.type = 'range';
        brightnessSlider.min = '0.1';
        brightnessSlider.max = '1';
        brightnessSlider.step = '0.05';
        brightnessSlider.value = brightnessLevel;
        brightnessSlider.className = 'brightness-slider';
        brightnessSlider.id = 'brightness-slider';

        // Create contrast slider
        const contrastLabel = document.createElement('div');
        contrastLabel.className = 'slider-label';
        contrastLabel.innerHTML = `
            <span>Contrast</span>
            <span class="slider-value" id="contrast-value">${Math.round(contrastLevel * 100)}%</span>
        `;

        const contrastSlider = document.createElement('input');
        contrastSlider.type = 'range';
        contrastSlider.min = '0.8';
        contrastSlider.max = '1.5';
        contrastSlider.step = '0.05';
        contrastSlider.value = contrastLevel;
        contrastSlider.className = 'brightness-slider';
        contrastSlider.id = 'contrast-slider';

        // Create sepia slider
        const sepiaLabel = document.createElement('div');
        sepiaLabel.className = 'slider-label';
        sepiaLabel.innerHTML = `
            <span>Warmth</span>
            <span class="slider-value" id="sepia-value">${Math.round(sepiaLevel * 100)}%</span>
        `;

        const sepiaSlider = document.createElement('input');
        sepiaSlider.type = 'range';
        sepiaSlider.min = '0';
        sepiaSlider.max = '0.5';
        sepiaSlider.step = '0.05';
        sepiaSlider.value = sepiaLevel;
        sepiaSlider.className = 'brightness-slider';
        sepiaSlider.id = 'sepia-slider';

        // Reset button
        const resetButton = document.createElement('button');
        resetButton.className = 'mt-3 w-full px-3 py-1 bg-gray-700 text-gray-200 rounded text-sm hover:bg-gray-600';
        resetButton.textContent = 'Reset to Default';
        resetButton.style.cursor = 'pointer';

        // Close button
        const closeButton = document.createElement('button');
        closeButton.className = 'p-1 text-gray-400 hover:text-gray-200 absolute top-2 right-2';
        closeButton.innerHTML = '×';
        closeButton.style.cssText = 'font-size: 16px; cursor: pointer; background: none; border: none;';

        // Title
        const title = document.createElement('div');
        title.className = 'text-center mb-3 text-sm font-medium';
        title.textContent = 'Display Settings';
        title.style.color = '#f8e3c5';

        // Add elements to container
        container.appendChild(closeButton);
        container.appendChild(title);

        container.appendChild(brightnessLabel);
        container.appendChild(brightnessSlider);

        container.appendChild(contrastLabel);
        container.appendChild(contrastSlider);

        container.appendChild(sepiaLabel);
        container.appendChild(sepiaSlider);

        container.appendChild(resetButton);

        // Add event listeners for sliders
        brightnessSlider.addEventListener('input', function() {
            brightnessLevel = this.value;
            document.getElementById('brightness-value').textContent = Math.round(brightnessLevel * 100) + '%';
            localStorage.setItem('pdf-brightness', brightnessLevel);
            updateFilters();
        });

        contrastSlider.addEventListener('input', function() {
            contrastLevel = this.value;
            document.getElementById('contrast-value').textContent = Math.round(contrastLevel * 100) + '%';
            localStorage.setItem('pdf-contrast', contrastLevel);
            updateFilters();
        });

        sepiaSlider.addEventListener('input', function() {
            sepiaLevel = this.value;
            document.getElementById('sepia-value').textContent = Math.round(sepiaLevel * 100) + '%';
            localStorage.setItem('pdf-sepia', sepiaLevel);
            updateFilters();
        });

        // Reset button event listener
        resetButton.addEventListener('click', function() {
            // Reset to defaults
            brightnessLevel = 0.5;
            contrastLevel = 1.2;
            sepiaLevel = 0.3;

            // Update UI
            document.getElementById('brightness-value').textContent = Math.round(brightnessLevel * 100) + '%';
            document.getElementById('brightness-slider').value = brightnessLevel;

            document.getElementById('contrast-value').textContent = Math.round(contrastLevel * 100) + '%';
            document.getElementById('contrast-slider').value = contrastLevel;

            document.getElementById('sepia-value').textContent = Math.round(sepiaLevel * 100) + '%';
            document.getElementById('sepia-slider').value = sepiaLevel;

            // Save to localStorage
            localStorage.setItem('pdf-brightness', brightnessLevel);
            localStorage.setItem('pdf-contrast', contrastLevel);
            localStorage.setItem('pdf-sepia', sepiaLevel);

            // Update filters
            updateFilters();
        });

        // Close button event listener
        closeButton.addEventListener('click', function() {
            container.classList.remove('visible');
        });

        // Add container to the page
        document.body.appendChild(container);

        // Make sure it's shown when the page loads if we're in dark mode
        if (isDarkMode) {
            setTimeout(() => {
                container.classList.add('visible');
            }, 1000);
        }
    }

    // Function to update filters based on slider values
    function updateFilters() {
        try {
            const iframeDoc = pdfViewer.contentDocument || pdfViewer.contentWindow.document;

            if (!iframeDoc) return;

            // Update CSS variables in the iframe
            const root = iframeDoc.documentElement;
            root.style.setProperty('--brightness-level', brightnessLevel);
            root.style.setProperty('--contrast-level', contrastLevel);
            root.style.setProperty('--sepia-level', sepiaLevel);

            // Additionally force update on all canvas elements
            const canvases = iframeDoc.querySelectorAll('.pdfViewer .page canvas');
            canvases.forEach(canvas => {
                canvas.style.filter = `brightness(${brightnessLevel}) contrast(${contrastLevel}) sepia(${sepiaLevel})`;
            });
        } catch (error) {
            console.error('Failed to update filters:', error);
        }
    }

    // Function to inject dark mode CSS
    const setupDarkMode = () => {
        try {
            // Get the iframe's document
            const iframeDoc = pdfViewer.contentDocument || pdfViewer.contentWindow.document;
            const iframeWin = pdfViewer.contentWindow;

            // If we can't access the iframe content, it might be due to cross-origin restrictions
            if (!iframeDoc || !iframeWin) {
                console.error('Cannot access iframe content - possible cross-origin restriction');
                return;
            }

            // Inject a global function to toggle brightness controls in the iframe
            const injectScript = iframeDoc.createElement('script');
            injectScript.textContent = `
                // Global function to toggle brightness controls
                window.toggleBrightnessControls = function() {
                    const container = document.getElementById('brightness-control-container');
                    if (container) {
                        container.classList.toggle('visible');
                    } else {
                        console.error('Brightness control container not found in iframe');
                        // Send message to parent to toggle instead
                        window.parent.postMessage({ type: 'toggleBrightnessFromIframe' }, '*');
                    }
                };
            `;
            iframeDoc.body.appendChild(injectScript);

            // Create link element for the dark mode CSS
            let darkModeLink = iframeDoc.getElementById('pdf-dark-mode-css');

            if (!darkModeLink) {
                darkModeLink = iframeDoc.createElement('link');
                darkModeLink.rel = 'stylesheet';
                darkModeLink.href = '/css/pdf-dark-mode.css';
                darkModeLink.id = 'pdf-dark-mode-css';
                iframeDoc.head.appendChild(darkModeLink);
            }

            // Toggle visibility based on current mode
            darkModeLink.disabled = !isDarkMode;

            // Apply the improved dark mode class to the viewer container
            const viewerContainer = iframeDoc.getElementById('viewerContainer');
            if (viewerContainer) {
                if (isDarkMode) {
                    viewerContainer.classList.add('dark-mode-improved');

                    // Additional force for pure black background
                    const style = iframeDoc.createElement('style');
                    style.id = 'dark-mode-extra-styles';
                    style.textContent = `
                        .pdfViewer .page { background-color: #000 !important; }
                        .textLayer span { color: #f8e3c5 !important; }
                        #viewerContainer, body, .pdfPresentationMode { background-color: #000 !important; }
                    `;

                    // Remove any existing extra styles before adding new ones
                    const existingExtraStyles = iframeDoc.getElementById('dark-mode-extra-styles');
                    if (existingExtraStyles) {
                        existingExtraStyles.remove();
                    }

                    iframeDoc.head.appendChild(style);

                    // Update CSS variables for filters
                    const root = iframeDoc.documentElement;
                    root.style.setProperty('--brightness-level', brightnessLevel);
                    root.style.setProperty('--contrast-level', contrastLevel);
                    root.style.setProperty('--sepia-level', sepiaLevel);
                } else {
                    viewerContainer.classList.remove('dark-mode-improved');

                    // Remove extra styles when light mode is enabled
                    const existingExtraStyles = iframeDoc.getElementById('dark-mode-extra-styles');
                    if (existingExtraStyles) {
                        existingExtraStyles.remove();
                    }
                }
            }

            // If dark mode is enabled, update filters
            if (isDarkMode) {
                updateFilters();
            }

            console.log('PDF dark mode setup completed. Dark mode is', isDarkMode ? 'ON' : 'OFF');
        } catch (error) {
            console.error('Failed to setup PDF dark mode:', error);
        }
    };

    // Listen for storage events to sync dark mode state across components
    window.addEventListener('storage', function(e) {
        if (e.key === 'pdf-dark-mode') {
            isDarkMode = e.newValue === 'true';
            if (headerDarkModeToggle) {
                updateToggleButton();
            }
            setupDarkMode();

            // Update brightness controls visibility
            const brightnessContainer = document.getElementById('brightness-control-container');
            if (brightnessContainer) {
                if (isDarkMode) {
                    brightnessContainer.classList.add('visible');
                } else {
                    brightnessContainer.classList.remove('visible');
                }
            }
        } else if (e.key === 'pdf-brightness' || e.key === 'pdf-contrast' || e.key === 'pdf-sepia') {
            // Update values when storage changes
            if (e.key === 'pdf-brightness') brightnessLevel = e.newValue || 0.5;
            if (e.key === 'pdf-contrast') contrastLevel = e.newValue || 1.2;
            if (e.key === 'pdf-sepia') sepiaLevel = e.newValue || 0.3;

            // Update UI
            if (document.getElementById('brightness-value')) {
                document.getElementById('brightness-value').textContent = Math.round(brightnessLevel * 100) + '%';
                document.getElementById('brightness-slider').value = brightnessLevel;
            }

            if (document.getElementById('contrast-value')) {
                document.getElementById('contrast-value').textContent = Math.round(contrastLevel * 100) + '%';
                document.getElementById('contrast-slider').value = contrastLevel;
            }

            if (document.getElementById('sepia-value')) {
                document.getElementById('sepia-value').textContent = Math.round(sepiaLevel * 100) + '%';
                document.getElementById('sepia-slider').value = sepiaLevel;
            }

            // Update filters
            updateFilters();
        }
    });

    // Function to retry setup multiple times
    const retrySetup = (maxAttempts, delay) => {
        let attempts = 0;

        const trySetup = () => {
            attempts++;
            console.log(`Dark mode setup attempt ${attempts}/${maxAttempts}`);

            setupDarkMode();

            // Retry if not successful and attempts remain
            if (attempts < maxAttempts) {
                setTimeout(trySetup, delay);
            }
        };

        trySetup();
    };

    // Add a load event listener to the iframe
    pdfViewer.addEventListener('load', function() {
        // Retry setup multiple times with increasing delays
        retrySetup(5, 1000);
    });

    // Try to inject immediately in case the iframe is already loaded
    setTimeout(() => retrySetup(3, 1000), 500);
});
