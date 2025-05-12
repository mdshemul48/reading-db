<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Notes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Notes Slider Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Notes</h3>
                    <div class="notes-slider relative">
                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                                <!-- Notes will be loaded here dynamically -->
                            </div>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>

            <!-- All Notes Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">All Notes</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="all-notes">
                        <!-- Notes will be loaded here dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Swiper JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <style>
        .notes-slider {
            padding: 20px 0;
        }

        .swiper-container {
            width: 100%;
            padding-top: 20px;
            padding-bottom: 50px;
        }

        .swiper-slide {
            background-position: center;
            background-size: cover;
            width: 300px;
            height: 300px;
        }

        .note-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .note-card:hover {
            transform: translateY(-5px);
        }

        .note-book {
            font-size: 0.875rem;
            color: #6B7280;
            margin-bottom: 10px;
        }

        .note-text {
            font-size: 1rem;
            color: #1F2937;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .note-highlight {
            background-color: #F3F4F6;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-style: italic;
        }

        .note-meta {
            font-size: 0.75rem;
            color: #9CA3AF;
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: #4B5563;
        }

        .swiper-pagination-bullet-active {
            background: #4B5563;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Swiper
            const swiper = new Swiper('.swiper-container', {
                effect: 'coverflow',
                grabCursor: true,
                centeredSlides: true,
                slidesPerView: 'auto',
                coverflowEffect: {
                    rotate: 50,
                    stretch: 0,
                    depth: 100,
                    modifier: 1,
                    slideShadows: true,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });

            // Load notes
            fetchNotes();

            function fetchNotes() {
                fetch('{{ route('notes.all') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            renderSliderNotes(data.notes);
                            renderAllNotes(data.notes);
                        }
                    })
                    .catch(error => console.error('Error loading notes:', error));
            }

            function renderSliderNotes(notes) {
                const sliderWrapper = document.querySelector('.swiper-wrapper');
                sliderWrapper.innerHTML = '';

                // Take only the first 6 notes for the slider
                const sliderNotes = notes.slice(0, 6);

                sliderNotes.forEach(note => {
                    const slide = document.createElement('div');
                    slide.className = 'swiper-slide';
                    slide.innerHTML = `
                        <div class="note-card">
                            <div class="note-book">${note.book ? note.book.title : 'No Book'}</div>
                            ${note.text_content ? `<div class="note-highlight">${note.text_content}</div>` : ''}
                            <div class="note-text">${note.note}</div>
                            <div class="note-meta">
                                Page ${note.page_number} • ${new Date(note.created_at).toLocaleDateString()}
                            </div>
                        </div>
                    `;
                    sliderWrapper.appendChild(slide);
                });

                // Update Swiper
                swiper.update();
            }

            function renderAllNotes(notes) {
                const allNotesContainer = document.getElementById('all-notes');
                allNotesContainer.innerHTML = '';

                notes.forEach(note => {
                    const noteElement = document.createElement('div');
                    noteElement.className = 'note-card';
                    noteElement.innerHTML = `
                        <div class="note-book">${note.book ? note.book.title : 'No Book'}</div>
                        ${note.text_content ? `<div class="note-highlight">${note.text_content}</div>` : ''}
                        <div class="note-text">${note.note}</div>
                        <div class="note-meta">
                            Page ${note.page_number} • ${new Date(note.created_at).toLocaleDateString()}
                        </div>
                    `;
                    allNotesContainer.appendChild(noteElement);
                });
            }
        });
    </script>
</x-app-layout>
