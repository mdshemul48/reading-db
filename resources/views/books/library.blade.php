<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Public Library') }}
            </h2>
            <a href="{{ route('books.create') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Upload Book
            </a>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($books->isEmpty())
                        <p class="text-gray-500">There are no public books available yet.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-4">
                            @foreach ($books as $book)
                                <div
                                    class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                    <div class="h-40 bg-gray-100 flex items-center justify-center overflow-hidden">
                                        @if ($book->thumbnail_path)
                                            <img src="{{ Storage::url($book->thumbnail_path) }}"
                                                alt="{{ $book->title }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z">
                                                </path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <h4 class="font-semibold text-lg truncate">{{ $book->title }}</h4>
                                        @if ($book->author)
                                            <p class="text-gray-600 text-sm mb-1">By {{ $book->author }}</p>
                                        @endif
                                        <p class="text-gray-600 text-sm mb-2">Uploaded by {{ $book->user->name }}
                                            {{ $book->created_at->diffForHumans() }}</p>
                                        <p class="text-gray-800 text-sm mb-2 line-clamp-2">{{ $book->description }}</p>
                                        <div class="flex justify-between items-center mt-3">
                                            @if (auth()->check() && auth()->user()->isEnrolledIn($book))
                                                <span
                                                    class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">Enrolled</span>
                                            @else
                                                <form action="{{ route('books.enroll', $book) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-xs px-2 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-800 rounded">
                                                        Enroll
                                                    </button>
                                                </form>
                                            @endif
                                            <div class="flex space-x-2">
                                                <a href="{{ route('books.show', $book) }}"
                                                    class="text-blue-600 hover:text-blue-800">View</a>
                                                @if ($book->user_id === auth()->id())
                                                    <a href="{{ route('books.edit', $book) }}"
                                                        class="text-emerald-600 hover:text-emerald-800">Edit</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $books->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
