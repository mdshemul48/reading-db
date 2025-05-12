<!-- Main PDF container - takes up full viewport height -->
<div class="pdf-container-wrapper" style="height: 100vh; padding-top: 0">
    <div id="pdfjs-container" class="w-full h-full">
        @php
            $initialPage = $enrollment ? $enrollment->current_page : 1;
            $initialScrollPosition =
                $enrollment && $enrollment->scroll_position !== null ? $enrollment->scroll_position : null;
        @endphp
        <iframe id="pdf-viewer" data-initial-page="{{ $initialPage }}"
            src="{{ asset('pdfjs/web/viewer.html') }}?file={{ urlencode($pdfUrl) }}" width="100%" height="100%"
            frameborder="0"></iframe>
    </div>
</div>
