<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $book->title }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('books.download', $book) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Download PDF
                </a>
                @if ($isEnrolled || $book->user_id === auth()->id())
                    <a href="{{ route('books.reader', $book) }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Read Book
                    </a>
                @endif
                @if ($book->user_id === auth()->id() || auth()->user()->isAdmin())
                    <a href="{{ route('books.edit', $book) }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Edit Book
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-300 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div class="mb-4 p-4 bg-blue-100 text-blue-700 border border-blue-300 rounded">
                    {{ session('info') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-300 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between items-start mb-6">
                        <div class="flex flex-col md:flex-row mb-4 md:mb-0">
                            <div class="mr-0 md:mr-6 mb-4 md:mb-0 flex-shrink-0">
                                <div
                                    class="h-48 w-36 bg-gray-100 rounded overflow-hidden flex items-center justify-center">
                                    @if ($book->thumbnail_path)
                                        <img src="{{ Storage::url($book->thumbnail_path) }}" alt="{{ $book->title }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z">
                                            </path>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold mb-2">{{ $book->title }}</h1>
                                <p class="text-gray-600 mb-1">{{ $book->author ? 'By ' . $book->author : '' }}</p>
                                <p class="text-gray-600">Uploaded by {{ $book->user->name }}
                                    {{ $book->created_at->diffForHumans() }}</p>

                                <div class="mt-2 flex items-center space-x-4">
                                    <span
                                        class="px-2 py-1 text-xs rounded {{ $book->is_private ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $book->is_private ? 'Private' : 'Public' }}
                                    </span>

                                    @if ($isEnrolled)
                                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                            Enrolled
                                        </span>
                                    @endif
                                </div>

                                @if ($isEnrolled && isset($enrollment) && $enrollment->total_pages)
                                    <div class="mt-3">
                                        <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                                            <span>Reading Progress:</span>
                                            <span>{{ $enrollment->current_page }} of {{ $enrollment->total_pages }}
                                                pages
                                                ({{ round(($enrollment->current_page / $enrollment->total_pages) * 100) }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                style="width: {{ round(($enrollment->current_page / $enrollment->total_pages) * 100) }}%">
                                            </div>
                                        </div>
                                        @if ($enrollment->last_read_at)
                                            <div class="mt-1 text-xs text-gray-500">
                                                Last read
                                                {{ \Carbon\Carbon::parse($enrollment->last_read_at)->diffForHumans() }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if (!$isEnrolled && ($book->user_id !== auth()->id() || auth()->user()->isAdmin()))
                            <form action="{{ route('books.enroll', $book) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Enroll in this Book
                                </button>
                            </form>
                        @elseif ($isEnrolled)
                            <button
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                onclick="openUnenrollModal()">
                                Unenroll from this Book
                            </button>
                        @endif
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold mb-3">Description</h3>
                        <div class="prose max-w-none">
                            {{ $book->description ?: 'No description provided.' }}
                        </div>
                    </div>

                    @if ($book->user_id === auth()->id() || auth()->user()->isAdmin())
                        <div class="mt-8 flex justify-between items-center pt-6 border-t">
                            <div class="space-x-2">
                                <a href="{{ route('books.edit', $book) }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Edit Book
                                </a>
                                <a href="{{ route('books.enrollments', $book) }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Manage Enrollments
                                </a>
                            </div>

                            <button type="button" onclick="openDeleteModal()"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Delete Book
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Unenroll Confirmation Modal -->
<div id="unenrollModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-96 max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Unenrollment</h3>
        <p class="text-sm text-gray-600 mb-4">For security purposes, please enter your password to confirm that you want
            to unenroll from <span class="font-medium">{{ $book->title }}</span>.</p>

        <form action="{{ route('books.unenroll', $book) }}" method="POST">
            @csrf
            @method('DELETE')

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <div id="password-error" class="mt-1 text-sm text-red-600 hidden">Password is required</div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeUnenrollModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Confirm Unenrollment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUnenrollModal() {
        document.getElementById('unenrollModal').classList.remove('hidden');
        document.getElementById('password').focus();
    }

    function closeUnenrollModal() {
        document.getElementById('unenrollModal').classList.add('hidden');
        document.getElementById('password').value = '';
        document.getElementById('password-error').classList.add('hidden');
    }

    function openDeleteModal() {
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('delete-password').focus();
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('delete-password').value = '';
        document.getElementById('delete-password-error').classList.add('hidden');
    }

    // Add form validation
    document.addEventListener('DOMContentLoaded', function() {
        const unenrollForm = document.querySelector('#unenrollModal form');
        const deleteForm = document.querySelector('#deleteModal form');

        unenrollForm.addEventListener('submit', function(e) {
            const passwordInput = document.getElementById('password');
            const passwordError = document.getElementById('password-error');

            if (!passwordInput.value.trim()) {
                e.preventDefault();
                passwordError.classList.remove('hidden');
                passwordInput.focus();
            }
        });

        deleteForm.addEventListener('submit', function(e) {
            const passwordInput = document.getElementById('delete-password');
            const passwordError = document.getElementById('delete-password-error');

            if (!passwordInput.value.trim()) {
                e.preventDefault();
                passwordError.classList.remove('hidden');
                passwordInput.focus();
            }
        });

        // Hide errors when typing
        document.getElementById('password').addEventListener('input', function() {
            document.getElementById('password-error').classList.add('hidden');
        });

        document.getElementById('delete-password').addEventListener('input', function() {
            document.getElementById('delete-password-error').classList.add('hidden');
        });
    });
</script>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-96 max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Book Deletion</h3>
        <p class="text-sm text-gray-600 mb-4">
            <span class="font-bold text-red-600">Warning:</span> This action cannot be undone. All data related to this
            book will be permanently deleted.
        </p>
        <p class="text-sm text-gray-600 mb-4">
            For security purposes, please enter your password to confirm that you want to delete
            <span class="font-medium">{{ $book->title }}</span>.
        </p>

        <form action="{{ route('books.destroy', $book) }}" method="POST">
            @csrf
            @method('DELETE')

            <div class="mb-4">
                <label for="delete-password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="delete-password" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <div id="delete-password-error" class="mt-1 text-sm text-red-600 hidden">Password is required</div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDeleteModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Permanently Delete Book
                </button>
            </div>
        </form>
    </div>
</div>
