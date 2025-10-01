<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Multiple Websites (Bulk)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Instructions -->
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                        <p class="text-sm text-blue-800">
                            Add multiple websites at once. Click 'Add Another Website' to add more entries. Leave unused rows empty.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('websites.bulk-store') }}" class="p-6 space-y-6">
                        @csrf
                        
                        <!-- Dynamic Form Container -->
                        <div id="websites-container">
                            @php
                                $oldWebsites = old('websites', []);
                                $websiteCount = count($oldWebsites) > 0 ? count($oldWebsites) : 3;
                            @endphp
                            
                            @for($i = 0; $i < $websiteCount; $i++)
                                @php
                                    $oldWebsites = old('websites', null);
                                    $isPostback = $oldWebsites !== null;
                                    $oldActive = old("websites.$i.is_active");
                                    
                                    // Determine checked state:
                                    // - If no old input exists (first visit), default to checked
                                    // - If old input exists, use the actual old value (null = unchecked, 1 = checked)
                                    $shouldBeChecked = $isPostback ? ($oldActive === '1' || $oldActive === 1) : true;
                                @endphp
                                
                                <div class="website-entry border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50" data-index="{{ $i }}">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-sm font-semibold text-gray-700">Website #<span class="website-number">{{ $i + 1 }}</span></h3>
                                        <button type="button" class="remove-website text-red-600 hover:text-red-800 text-sm font-medium">Remove</button>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label for="websites[{{ $i }}][name]" class="block font-medium text-sm text-gray-700">Website Name</label>
                                            <input id="websites[{{ $i }}][name]" name="websites[{{ $i }}][name]" type="text" value="{{ old("websites.{$i}.name") }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" />
                                            @error("websites.{$i}.name")
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="websites[{{ $i }}][url]" class="block font-medium text-sm text-gray-700">Website URL</label>
                                            <input id="websites[{{ $i }}][url]" name="websites[{{ $i }}][url]" type="url" value="{{ old("websites.{$i}.url") }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" placeholder="https://example.com" />
                                            @error("websites.{$i}.url")
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="websites[{{ $i }}][is_active]" class="block font-medium text-sm text-gray-700">Status</label>
                                            <div class="mt-2">
                                                <input type="checkbox" id="websites[{{ $i }}][is_active]" name="websites[{{ $i }}][is_active]" value="1" {{ $shouldBeChecked ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                                <span class="ms-2 text-sm text-gray-600">Active</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <!-- Add Another Website Button -->
                        <div class="flex justify-start">
                            <button type="button" id="add-website" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Another Website
                            </button>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Add All Websites') }}</x-primary-button>
                            
                            <a href="{{ route('websites.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get initial count from server-side rendered form
        let websiteCount = document.querySelectorAll('.website-entry').length;

        // Add new website row
        document.getElementById('add-website').addEventListener('click', function() {
            addWebsiteRow();
            updateWebsiteNumbers();
        });

        // Add remove functionality to existing rows
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.remove-website').forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('.website-entry');
                    if (document.querySelectorAll('.website-entry').length > 1) {
                        row.remove();
                        updateWebsiteNumbers();
                    } else {
                        alert('You must have at least one website entry.');
                    }
                });
            });
        });

        // Function to add a new website entry row
        function addWebsiteRow() {
            const container = document.getElementById('websites-container');
            const index = websiteCount;
            
            const row = document.createElement('div');
            row.className = 'website-entry border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50';
            row.setAttribute('data-index', index);
            row.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Website #<span class="website-number">${index + 1}</span></h3>
                    <button type="button" class="remove-website text-red-600 hover:text-red-800 text-sm font-medium">Remove</button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="websites[${index}][name]" class="block font-medium text-sm text-gray-700">Website Name</label>
                        <input id="websites[${index}][name]" name="websites[${index}][name]" type="text" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" />
                    </div>
                    
                    <div>
                        <label for="websites[${index}][url]" class="block font-medium text-sm text-gray-700">Website URL</label>
                        <input id="websites[${index}][url]" name="websites[${index}][url]" type="url" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" placeholder="https://example.com" />
                    </div>
                    
                    <div>
                        <label for="websites[${index}][is_active]" class="block font-medium text-sm text-gray-700">Status</label>
                        <div class="mt-2">
                            <input type="checkbox" id="websites[${index}][is_active]" name="websites[${index}][is_active]" value="1" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="ms-2 text-sm text-gray-600">Active</span>
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(row);
            websiteCount++;
            
            // Add remove functionality to the new row
            row.querySelector('.remove-website').addEventListener('click', function() {
                if (document.querySelectorAll('.website-entry').length > 1) {
                    row.remove();
                    updateWebsiteNumbers();
                } else {
                    alert('You must have at least one website entry.');
                }
            });
        }

        // Update website numbers after add/remove
        function updateWebsiteNumbers() {
            const entries = document.querySelectorAll('.website-entry');
            entries.forEach((entry, index) => {
                entry.querySelector('.website-number').textContent = index + 1;
                // Update data-index attribute
                entry.setAttribute('data-index', index);
                
                // Update all input names and IDs to match new index
                const inputs = entry.querySelectorAll('input, label');
                inputs.forEach(input => {
                    if (input.id) {
                        input.id = input.id.replace(/websites\[\d+\]/, `websites[${index}]`);
                    }
                    if (input.name) {
                        input.name = input.name.replace(/websites\[\d+\]/, `websites[${index}]`);
                    }
                    if (input.getAttribute('for')) {
                        input.setAttribute('for', input.getAttribute('for').replace(/websites\[\d+\]/, `websites[${index}]`));
                    }
                });
            });
        }
    </script>
</x-app-layout>
