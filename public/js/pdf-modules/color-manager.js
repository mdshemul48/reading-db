// PDF Color Manager Module
// Handles color themes, brightness, warmth and other visual preferences

const ColorManager = (function() {
    // Default color presets
    const colorPresets = {
        light: {
            name: 'Light',
            bgColor: '#ffffff',
            textColor: '#1f2937',
            borderColor: '#e5e7eb',
            pdfBg: '#f3f4f6',
            mutedBg: '#f9fafb',
            primaryColor: '#3b82f6',
            hoverColor: '#f3f4f6',
            scrollbarThumb: '#cbd5e1',
            scrollbarTrack: '#f1f5f9',
            filterSettings: {
                invert: 0,
                hueRotate: 0,
                sepia: 0,
                brightness: 100,
                contrast: 100
            }
        },
        dark: {
            name: 'Dark',
            bgColor: '#1f2937',
            textColor: '#f9fafb',
            borderColor: '#374151',
            pdfBg: '#111827',
            mutedBg: '#374151',
            primaryColor: '#60a5fa',
            hoverColor: '#374151',
            scrollbarThumb: '#4b5563',
            scrollbarTrack: '#1f2937',
            filterSettings: {
                invert: 90,
                hueRotate: 180,
                sepia: 0,
                brightness: 100,
                contrast: 100
            }
        },
        warmDark: {
            name: 'Warm Dark',
            bgColor: '#302b20',
            textColor: '#fff2d9',
            borderColor: '#45402e',
            pdfBg: '#272216',
            mutedBg: '#3b3424',
            primaryColor: '#ffc24b',
            hoverColor: '#45402e',
            scrollbarThumb: '#6b5e35',
            scrollbarTrack: '#302b20',
            filterSettings: {
                invert: 90,
                hueRotate: 180,
                sepia: 30,
                brightness: 85,
                contrast: 90
            }
        },
        sepia: {
            name: 'Sepia',
            bgColor: '#f8f2e4',
            textColor: '#5f4b32',
            borderColor: '#e0d6c2',
            pdfBg: '#f4ead9',
            mutedBg: '#f0e6d2',
            primaryColor: '#a67c52',
            hoverColor: '#e8dcc7',
            scrollbarThumb: '#c4b59e',
            scrollbarTrack: '#f0e6d2',
            filterSettings: {
                invert: 0,
                hueRotate: 0,
                sepia: 50,
                brightness: 100,
                contrast: 95
            }
        },
        darkBlue: {
            name: 'Dark Blue',
            bgColor: '#172a46',
            textColor: '#e6f0ff',
            borderColor: '#2d4a6d',
            pdfBg: '#0e1c30',
            mutedBg: '#1e3855',
            primaryColor: '#4a9fff',
            hoverColor: '#2d4a6d',
            scrollbarThumb: '#3d5a7d',
            scrollbarTrack: '#172a46',
            filterSettings: {
                invert: 90,
                hueRotate: 210,
                sepia: 10,
                brightness: 90,
                contrast: 95
            }
        },
        green: {
            name: 'Green',
            bgColor: '#f3f9f5',
            textColor: '#2c4a3e',
            borderColor: '#d1e7db',
            pdfBg: '#edf7f1',
            mutedBg: '#e1f1e7',
            primaryColor: '#38a169',
            hoverColor: '#d7efe2',
            scrollbarThumb: '#a0d8bb',
            scrollbarTrack: '#e1f1e7',
            filterSettings: {
                invert: 0,
                hueRotate: 0,
                sepia: 10,
                brightness: 105,
                contrast: 90
            }
        },
        darkGreen: {
            name: 'Dark Green',
            bgColor: '#1e2d27',
            textColor: '#e2f2eb',
            borderColor: '#304c40',
            pdfBg: '#15211c',
            mutedBg: '#2a3f35',
            primaryColor: '#4fd1a5',
            hoverColor: '#304c40',
            scrollbarThumb: '#3a5c4d',
            scrollbarTrack: '#1e2d27',
            filterSettings: {
                invert: 90,
                hueRotate: 140,
                sepia: 15,
                brightness: 90,
                contrast: 95
            }
        }
    };

    // Current settings
    let currentPreset = 'light';
    let customSettings = {};
    let brightness = 100;
    let contrast = 100;
    let warmth = 0;

    // Initialize with saved preferences
    function init() {
        // Load saved preferences
        const savedPreset = localStorage.getItem('pdf-color-preset') || 'light';
        const savedBrightness = parseInt(localStorage.getItem('pdf-brightness') || '100');
        const savedContrast = parseInt(localStorage.getItem('pdf-contrast') || '100');
        const savedWarmth = parseInt(localStorage.getItem('pdf-warmth') || '0');

        // Set initial values
        currentPreset = savedPreset;
        brightness = savedBrightness;
        contrast = savedContrast;
        warmth = savedWarmth;

        // Apply current theme
        applyColorTheme();

        return {
            currentPreset,
            brightness,
            contrast,
            warmth
        };
    }

    // Apply the current color theme
    function applyColorTheme() {
        const root = document.documentElement;
        const preset = colorPresets[currentPreset];

        if (!preset) return;

        // Apply CSS variables
        root.style.setProperty('--bg-color', preset.bgColor);
        root.style.setProperty('--text-color', preset.textColor);
        root.style.setProperty('--border-color', preset.borderColor);
        root.style.setProperty('--pdf-bg', preset.pdfBg);
        root.style.setProperty('--muted-bg', preset.mutedBg);
        root.style.setProperty('--primary-color', preset.primaryColor);
        root.style.setProperty('--hover-color', preset.hoverColor);
        root.style.setProperty('--scrollbar-thumb', preset.scrollbarThumb);
        root.style.setProperty('--scrollbar-track', preset.scrollbarTrack);

        // Apply class for light/dark mode
        if (currentPreset === 'light' || currentPreset === 'sepia' || currentPreset === 'green') {
            document.body.classList.remove('dark-mode');
            document.body.classList.remove('cool-dark');
        } else {
            document.body.classList.add('dark-mode');

            // Handle cool vs warm dark mode
            if (currentPreset === 'dark' || currentPreset === 'darkBlue' || currentPreset === 'darkGreen') {
                document.body.classList.add('cool-dark');
            } else {
                document.body.classList.remove('cool-dark');
            }
        }

        // Apply filter settings to PDF content
        const filterSettings = {...preset.filterSettings};

        // Apply custom adjustments
        filterSettings.brightness = Math.max(50, Math.min(150, (filterSettings.brightness * brightness / 100)));
        filterSettings.contrast = Math.max(50, Math.min(150, (filterSettings.contrast * contrast / 100)));
        filterSettings.sepia = Math.max(0, Math.min(100, filterSettings.sepia + warmth));

        // Create CSS filter string
        const filterString = `
            invert(${filterSettings.invert}%)
            hue-rotate(${filterSettings.hueRotate}deg)
            sepia(${filterSettings.sepia}%)
            brightness(${filterSettings.brightness}%)
            contrast(${filterSettings.contrast}%)
        `;

        // Apply filter to canvas elements
        root.style.setProperty('--content-filter', filterString);

        // Apply the filter directly to any existing canvas elements for immediate effect
        const canvasElements = document.querySelectorAll('#pdf-viewer canvas');
        canvasElements.forEach(canvas => {
            canvas.style.filter = filterString;
        });

        // Save settings
        saveSettings();

        // Dispatch event for UI to respond to theme changes
        document.dispatchEvent(new CustomEvent('colorPresetChanged', {
            detail: {
                preset: currentPreset,
                isDark: currentPreset !== 'light' && currentPreset !== 'sepia' && currentPreset !== 'green'
            }
        }));

        // Update UI controls to match current settings
        updateUIControls();
    }

    // Update UI controls to match current settings
    function updateUIControls() {
        // Update preset selector
        const presetSelector = document.getElementById('color-preset-selector');
        if (presetSelector) presetSelector.value = currentPreset;

        // Update brightness slider
        const brightnessSlider = document.getElementById('brightness-slider');
        if (brightnessSlider) {
            brightnessSlider.value = brightness;
            const brightnessValue = document.getElementById('brightness-value');
            if (brightnessValue) brightnessValue.textContent = brightness + '%';
        }

        // Update contrast slider
        const contrastSlider = document.getElementById('contrast-slider');
        if (contrastSlider) {
            contrastSlider.value = contrast;
            const contrastValue = document.getElementById('contrast-value');
            if (contrastValue) contrastValue.textContent = contrast + '%';
        }

        // Update warmth slider
        const warmthSlider = document.getElementById('warmth-slider');
        if (warmthSlider) {
            warmthSlider.value = warmth;
            const warmthValue = document.getElementById('warmth-value');
            if (warmthValue) warmthValue.textContent = warmth + '%';
        }
    }

    // Change color preset
    function setColorPreset(preset) {
        if (colorPresets[preset]) {
            currentPreset = preset;
            applyColorTheme();
        }
    }

    // Adjust brightness (0-200)
    function setBrightness(value) {
        brightness = Math.max(0, Math.min(200, value));
        applyColorTheme();
    }

    // Adjust contrast (0-200)
    function setContrast(value) {
        contrast = Math.max(0, Math.min(200, value));
        applyColorTheme();
    }

    // Adjust warmth (0-100)
    function setWarmth(value) {
        warmth = Math.max(0, Math.min(100, value));
        applyColorTheme();
    }

    // Save current settings to localStorage
    function saveSettings() {
        try {
            localStorage.setItem('pdf-color-preset', currentPreset);
            localStorage.setItem('pdf-brightness', brightness.toString());
            localStorage.setItem('pdf-contrast', contrast.toString());
            localStorage.setItem('pdf-warmth', warmth.toString());

            console.log('Settings saved:', {
                preset: currentPreset,
                brightness,
                contrast,
                warmth
            });
        } catch (error) {
            console.error('Error saving settings:', error);
        }
    }

    // Get current settings
    function getSettings() {
        return {
            currentPreset,
            brightness,
            contrast,
            warmth,
            presets: Object.keys(colorPresets).map(key => ({
                id: key,
                name: colorPresets[key].name
            }))
        };
    }

    // Add event listeners to controls
    function setupControlListeners() {
        // Preset selector
        const presetSelector = document.getElementById('color-preset-selector');
        if (presetSelector) {
            presetSelector.addEventListener('change', function() {
                setColorPreset(this.value);
            });

            // Set initial value
            presetSelector.value = currentPreset;
        }

        // Brightness slider
        const brightnessSlider = document.getElementById('brightness-slider');
        if (brightnessSlider) {
            brightnessSlider.addEventListener('input', function() {
                setBrightness(parseInt(this.value));
                document.getElementById('brightness-value').textContent = this.value + '%';
            });

            // Set initial value
            brightnessSlider.value = brightness;
            document.getElementById('brightness-value').textContent = brightness + '%';
        }

        // Contrast slider
        const contrastSlider = document.getElementById('contrast-slider');
        if (contrastSlider) {
            contrastSlider.addEventListener('input', function() {
                setContrast(parseInt(this.value));
                document.getElementById('contrast-value').textContent = this.value + '%';
            });

            // Set initial value
            contrastSlider.value = contrast;
            document.getElementById('contrast-value').textContent = contrast + '%';
        }

        // Warmth slider
        const warmthSlider = document.getElementById('warmth-slider');
        if (warmthSlider) {
            warmthSlider.addEventListener('input', function() {
                setWarmth(parseInt(this.value));
                document.getElementById('warmth-value').textContent = this.value + '%';
            });

            // Set initial value
            warmthSlider.value = warmth;
            document.getElementById('warmth-value').textContent = warmth + '%';
        }
    }

    // Reset to defaults
    function resetToDefaults() {
        currentPreset = 'light';
        brightness = 100;
        contrast = 100;
        warmth = 0;
        applyColorTheme();

        // Update UI controls
        const presetSelector = document.getElementById('color-preset-selector');
        if (presetSelector) presetSelector.value = currentPreset;

        const brightnessSlider = document.getElementById('brightness-slider');
        if (brightnessSlider) {
            brightnessSlider.value = brightness;
            document.getElementById('brightness-value').textContent = brightness + '%';
        }

        const contrastSlider = document.getElementById('contrast-slider');
        if (contrastSlider) {
            contrastSlider.value = contrast;
            document.getElementById('contrast-value').textContent = contrast + '%';
        }

        const warmthSlider = document.getElementById('warmth-slider');
        if (warmthSlider) {
            warmthSlider.value = warmth;
            document.getElementById('warmth-value').textContent = warmth + '%';
        }
    }

    return {
        init,
        applyColorTheme,
        setColorPreset,
        setBrightness,
        setContrast,
        setWarmth,
        getSettings,
        setupControlListeners,
        resetToDefaults,
        updateUIControls
    };
})();
