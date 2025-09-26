<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Upload New File') }}
            </h2>
            <a href="{{ route('admin.files.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Files
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    <form action="{{ route('admin.files.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- File Upload -->
                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700">Select File *</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload a file</span>
                                            <input id="file" name="file" type="file" class="sr-only" required onchange="updateFileName(this)">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Any file type up to 50MB
                                    </p>
                                    <div id="file-name" class="text-sm text-gray-900 font-medium hidden"></div>
                                </div>
                            </div>
                            @error('file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Storage Disk -->
                        <div>
                            <label for="disk" class="block text-sm font-medium text-gray-700">Storage Location</label>
                            <select name="disk" 
                                    id="disk"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('disk') border-red-300 @enderror">
                                <option value="public" {{ old('disk', 'public') === 'public' ? 'selected' : '' }}>Public Storage</option>
                                <option value="local" {{ old('disk') === 'local' ? 'selected' : '' }}>Local Storage</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500">
                                Public storage allows direct access via URL. Local storage requires authentication.
                            </p>
                            @error('disk')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- File Information -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">File Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Original Name Override -->
                                <div>
                                    <label for="original_name_override" class="block text-sm font-medium text-gray-700">Display Name (Optional)</label>
                                    <input type="text" 
                                           name="original_name_override" 
                                           id="original_name_override"
                                           value="{{ old('original_name_override') }}"
                                           placeholder="Leave blank to use original filename"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('original_name_override') border-red-300 @enderror">
                                    <p class="mt-1 text-sm text-gray-500">
                                        Override the display name for this file
                                    </p>
                                    @error('original_name_override')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- File Category/Tags -->
                                <div>
                                    <label for="tags" class="block text-sm font-medium text-gray-700">Tags (Optional)</label>
                                    <input type="text" 
                                           name="tags" 
                                           id="tags"
                                           value="{{ old('tags') }}"
                                           placeholder="e.g., document, image, software"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('tags') border-red-300 @enderror">
                                    <p class="mt-1 text-sm text-gray-500">
                                        Comma-separated tags for organization
                                    </p>
                                    @error('tags')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Product Association -->
                        @if(isset($products) && $products->count() > 0)
                            <div class="border-t pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Associate with Products (Optional)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($products as $product)
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   name="product_ids[]" 
                                                   id="product_{{ $product->id }}"
                                                   value="{{ $product->id }}"
                                                   {{ in_array($product->id, old('product_ids', [])) ? 'checked' : '' }}
                                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            <label for="product_{{ $product->id }}" class="ml-3 block text-sm text-gray-700">
                                                {{ $product->title }}
                                                <span class="text-gray-500">(¥{{ number_format($product->price_cents / 100) }})</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('product_ids')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- Upload Progress -->
                        <div id="upload-progress" class="hidden">
                            <div class="bg-gray-200 rounded-full h-2">
                                <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <p id="progress-text" class="text-sm text-gray-600 mt-2">Uploading...</p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4 pt-6 border-t">
                            <a href="{{ route('admin.files.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                    id="submit-btn"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Upload File
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Upload Guidelines -->
            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <h3 class="text-lg font-medium text-yellow-800 mb-2">Upload Guidelines</h3>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• Maximum file size: 50MB</li>
                    <li>• All file types are supported</li>
                    <li>• Files are automatically scanned for security</li>
                    <li>• Use descriptive filenames for better organization</li>
                    <li>• Consider file size for digital products (affects download time)</li>
                </ul>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateFileName(input) {
            const fileNameDiv = document.getElementById('file-name');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                fileNameDiv.textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                fileNameDiv.classList.remove('hidden');
            } else {
                fileNameDiv.classList.add('hidden');
            }
        }

        // Handle form submission with progress
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            const progressDiv = document.getElementById('upload-progress');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
            progressDiv.classList.remove('hidden');
            
            // Simulate progress (in real implementation, you'd use XMLHttpRequest for actual progress)
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                
                document.getElementById('progress-bar').style.width = progress + '%';
                document.getElementById('progress-text').textContent = `Uploading... ${Math.round(progress)}%`;
                
                if (progress >= 90) {
                    clearInterval(interval);
                    document.getElementById('progress-text').textContent = 'Processing file...';
                }
            }, 200);
        });

        // Drag and drop functionality
        const dropZone = document.querySelector('.border-dashed');
        const fileInput = document.getElementById('file');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                fileInput.files = files;
                updateFileName(fileInput);
            }
        }
    </script>
    @endpush
</x-admin-layout>