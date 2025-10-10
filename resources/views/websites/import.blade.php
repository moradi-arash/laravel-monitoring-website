<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Websites from CSV') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'websites-csv-imported')
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    <div class="font-semibold">✓ Import completed!</div>
                    <div class="mt-1">
                        Successfully imported {{ session('imported_count') }} websites.
                        @if(session('error_count') > 0)
                            {{ session('error_count') }} rows had errors.
                        @endif
                    </div>
                    @if(session('import_errors') && count(session('import_errors')) > 0)
                        <div class="mt-2">
                            <details class="mt-2">
                                <summary class="cursor-pointer text-sm font-medium">View errors</summary>
                                <ul class="mt-1 text-sm list-disc list-inside">
                                    @foreach(session('import_errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        </div>
                    @endif
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Instructions -->
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                        <h3 class="text-sm font-semibold text-blue-800 mb-2">CSV Format Instructions:</h3>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• <strong>Column 1:</strong> Name (required) - Website display name</li>
                            <li>• <strong>Column 2:</strong> URL (required) - Valid website URL (e.g., https://example.com)</li>
                            <li>• <strong>Column 3:</strong> Is Active (optional) - 1 for active, 0 for inactive (default: 1)</li>
                            <li>• First row must contain headers: Name, URL, Is Active</li>
                            <li>• Maximum file size: 2MB</li>
                            <li>• Duplicate URLs will be skipped</li>
                        </ul>
                    </div>

                    <!-- Download Template Button -->
                    <div class="mb-6">
                        <a href="{{ route('websites.template') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download Sample CSV Template
                        </a>
                    </div>

                    <!-- Upload Form -->
                    <form method="POST" action="{{ route('websites.import.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label for="csv_file" class="block font-medium text-sm text-gray-700 mb-2">
                                Select CSV File
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="csv_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload a CSV file</span>
                                            <input id="csv_file" name="csv_file" type="file" accept=".csv,.txt" class="sr-only" required>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        CSV files up to 2MB
                                    </p>
                                </div>
                            </div>
                            @error('csv_file')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Import Websites') }}</x-primary-button>
                            
                            <a href="{{ route('websites.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>
                        </div>
                    </form>

                    <!-- Sample CSV Preview -->
                    <div class="mt-8">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Sample CSV Format:</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                            <pre class="text-sm text-gray-800 font-mono">Name,URL,Is Active
Example Site,https://example.com,1
Test Site,https://test.com,0
My Blog,https://myblog.com,1</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // File input change handler
        document.getElementById('csv_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
                
                // Update the upload area to show selected file
                const uploadArea = document.querySelector('.border-dashed');
                uploadArea.innerHTML = `
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">Selected file:</p>
                        <p class="text-sm font-medium text-gray-900">${fileName}</p>
                        <p class="text-xs text-gray-500">${fileSize} MB</p>
                    </div>
                `;
            }
        });
    </script>
</x-app-layout>
