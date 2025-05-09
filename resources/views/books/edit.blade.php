<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Book') }}: {{ $book->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('books.update', $book) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $book->title)" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Author -->
                        <div class="mt-4">
                            <x-input-label for="author" :value="__('Author')" />
                            <x-text-input id="author" class="block mt-1 w-full" type="text" name="author" :value="old('author', $book->author)" />
                            <x-input-error :messages="$errors->get('author')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description (optional)')" />
                            <textarea id="description" name="description" rows="4" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('description', $book->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Current PDF File -->
                        <div class="mt-4">
                            <x-input-label :value="__('Current PDF File')" />
                            <div class="mt-1 p-3 bg-gray-50 rounded border">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">{{ basename($book->file_path) }}</span>
                                    <a href="{{ route('books.download', $book) }}" class="text-blue-600 hover:text-blue-800 text-sm">Download</a>
                                </div>
                            </div>
                        </div>

                        <!-- Replace PDF File -->
                        <div class="mt-4">
                            <x-input-label for="pdf_file" :value="__('Replace PDF File (max 200MB, optional)')" />
                            <input id="pdf_file" type="file" name="pdf_file" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" accept=".pdf" />
                            <p class="mt-1 text-sm text-gray-500">Leave empty to keep the current file</p>
                            <x-input-error :messages="$errors->get('pdf_file')" class="mt-2" />
                        </div>

                        <!-- Current Thumbnail -->
                        @if($book->thumbnail_path)
                        <div class="mt-4">
                            <x-input-label :value="__('Current Thumbnail')" />
                            <div class="mt-1 p-3 bg-gray-50 rounded border">
                                <div class="flex items-center justify-between">
                                    <img src="{{ Storage::url($book->thumbnail_path) }}" alt="Thumbnail" class="h-24 object-contain">
                                    <span class="text-sm text-gray-700">{{ basename($book->thumbnail_path) }}</span>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Replace Thumbnail -->
                        <div class="mt-4">
                            <x-input-label for="thumbnail" :value="__('Replace Thumbnail (max 20MB, optional)')" />
                            <input id="thumbnail" type="file" name="thumbnail" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" accept="image/*" />
                            <p class="mt-1 text-sm text-gray-500">Supported formats: JPEG, PNG, JPG, GIF. Leave empty to keep the current thumbnail.</p>
                            <x-input-error :messages="$errors->get('thumbnail')" class="mt-2" />
                        </div>

                        <!-- Privacy Setting -->
                        <div class="mt-4">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_private" name="is_private" type="checkbox" value="1" class="w-4 h-4 border-gray-300 rounded text-indigo-600 focus:ring-indigo-500" {{ old('is_private', $book->is_private) ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_private" class="font-medium text-gray-700">Private book</label>
                                    <p class="text-gray-500">If checked, only you will be able to see this book. If unchecked, it will be visible in the public library.</p>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('is_private')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('books.show', $book) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Update Book') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 