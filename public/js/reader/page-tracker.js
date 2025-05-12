/**
 * PDF Reader Page Tracking Module
 * Handles page change events, updates progress, and saves reading progress
 */

// Handle page change events from PDF.js
function handlePageChange(message) {
    const currentPage = message.page;
    const totalPages = message.total || parseInt(document.getElementById('total-pages')?.textContent || 0);
    const scrollPosition = message.scrollPosition || null;

    // Update UI with null checks
    const currentPageEl = document.getElementById('current-page');
    const totalPagesEl = document.getElementById('total-pages');
    const progressBarEl = document.getElementById('progress-bar');
    const progressPercentageEl = document.getElementById('progress-percentage');

    if (currentPageEl) {
        currentPageEl.textContent = currentPage;
    }

    if (totalPagesEl) {
        totalPagesEl.textContent = totalPages;
    }

    // Only call updateProgress if we have valid elements
    if (progressBarEl && progressPercentageEl) {
        updateProgress(currentPage, totalPages, scrollPosition);
    }

    // Save progress
    saveProgressDebounced(currentPage, totalPages, scrollPosition);
}

// Update the progress UI
function updateProgress(currentPage, totalPages, scrollPosition) {
    if (!totalPages || isNaN(totalPages)) return;

    const progressPercentage = Math.round((currentPage / totalPages) * 100);
    const progressElement = document.getElementById('progress-percentage');
    const progressBarElement = document.getElementById('progress-bar');

    if (progressElement) {
        progressElement.textContent = progressPercentage;
    }

    if (progressBarElement) {
        progressBarElement.style.width = progressPercentage + '%';
    }
}

// Calculate reading duration
function calculateReadingDuration() {
    let duration = window.readerState.readingDuration;

    // If currently reading, add current session
    if (window.readerState.isReading) {
        const now = new Date();
        duration += (now - window.readerState.readingStartTime) / 60000; // convert to minutes
    }

    // Round to nearest minute, minimum 1 minute
    return Math.max(1, Math.round(duration));
}

// Debounce function for saving progress
const saveProgressDebounced = (function() {
    let timer;
    return function(currentPage, totalPages, scrollPosition) {
        clearTimeout(timer);
        timer = setTimeout(() => {
            saveProgress(currentPage, totalPages, scrollPosition);
        }, 500);
    };
})();

// Save progress to the server
function saveProgress(currentPage, totalPages, scrollPosition, isSynchronous = false) {
    // Calculate reading duration
    const durationMinutes = calculateReadingDuration();

    // After calculating duration, reset tracking
    if (durationMinutes > 0) {
        window.readerState.readingDuration = 0;
        window.readerState.readingStartTime = new Date();
    }

    const requestData = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.readerConfig.csrfToken
        },
        body: JSON.stringify({
            current_page: currentPage,
            total_pages: totalPages,
            scroll_position: scrollPosition,
            duration_minutes: durationMinutes,
            last_read_at: new Date().toISOString()
        })
    };

    // If synchronous (like beforeunload), use navigator.sendBeacon
    if (isSynchronous && navigator.sendBeacon) {
        const blob = new Blob([JSON.stringify({
            current_page: currentPage,
            total_pages: totalPages,
            scroll_position: scrollPosition,
            duration_minutes: durationMinutes,
            last_read_at: new Date().toISOString()
        })], {
            type: 'application/json'
        });

        navigator.sendBeacon(window.readerConfig.updateProgressUrl, blob);
        return;
    }

    // Otherwise use standard fetch
    fetch(window.readerConfig.updateProgressUrl, requestData)
        .then(response => response.json())
        .then(data => {
            console.log('Progress updated:', data);
        })
        .catch(error => {
            console.error('Error updating progress:', error);
        });
}

// Export functions for use in main.js
if (typeof module !== 'undefined') {
    module.exports = {
        handlePageChange,
        updateProgress,
        saveProgressDebounced,
        saveProgress,
        calculateReadingDuration
    };
}
