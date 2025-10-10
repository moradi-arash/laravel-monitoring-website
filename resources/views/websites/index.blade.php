<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Website Monitoring') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'website-created')
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    Website added successfully!
                </div>
            @endif

            @if (session('status') === 'website-updated')
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    Website updated successfully!
                </div>
            @endif

            @if (session('status') === 'website-deleted')
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    Website deleted successfully!
                </div>
            @endif

            @if (session('status') === 'websites-checked')
                <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-md">
                    ✓ Website monitoring check completed! All your active websites have been checked.
                </div>
            @endif

            @if (session('status') === 'no-websites-to-check')
                <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-md">
                    ⚠️ No active websites found to check. Please add some websites first.
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-md">
                    ⚠️ {{ session('warning') }}
                </div>
            @endif

            @if (session('status') === 'websites-bulk-created')
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    ✓ {{ session('count') }} websites added successfully!
                </div>
            @endif

            @if (session('status') === 'websites-csv-imported')
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    <div class="font-semibold">✓ CSV Import completed!</div>
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

            <div class="mb-6 flex items-center gap-4 flex-wrap">
                <a href="{{ route('websites.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Add New Website
                </a>
                
                <a href="{{ route('websites.bulk-create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Bulk
                </a>
                
                <a href="{{ route('websites.import') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    Import CSV
                </a>
                
                <a href="{{ route('websites.export') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export CSV
                </a>
                
                <form method="POST" action="{{ route('websites.check-now') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Check Now
                    </button>
                </form>
            </div>

            <!-- Search and Filter Form -->
            <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
                <form method="GET" action="{{ route('websites.index') }}" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search by name or URL..." 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <div class="min-w-[150px]">
                        <select name="status" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Filter
                        </button>
                        <a href="{{ route('websites.index') }}" 
                           class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" id="websites-container">
                    @include('websites.partials.website-list', ['websites' => $websites])
                </div>
            </div>

            @if($websites->hasPages())
                <div class="mt-6" id="pagination-container">
                    {{ $websites->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            const statusSelect = document.querySelector('select[name="status"]');
            const websitesContainer = document.getElementById('websites-container');
            const paginationContainer = document.getElementById('pagination-container');
            let searchTimeout;

            function performSearch() {
                const searchTerm = searchInput.value.trim();
                const status = statusSelect.value;
                
                // Only search if search term has 3+ characters or status is changed
                if (searchTerm.length >= 3 || status !== 'all') {
                    showLoading();
                    
                    fetch(`{{ route('websites.search') }}?search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(status)}`)
                        .then(response => response.json())
                        .then(data => {
                            websitesContainer.innerHTML = data.html;
                            if (data.pagination) {
                                paginationContainer.innerHTML = data.pagination;
                            } else {
                                paginationContainer.innerHTML = '';
                            }
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                            websitesContainer.innerHTML = '<div class="text-center py-12"><p class="text-red-500">خطا در جستجو. لطفاً دوباره تلاش کنید.</p></div>';
                        });
                } else if (searchTerm.length === 0 && status === 'all') {
                    // Reset to original state when search is cleared
                    location.reload();
                }
            }

            // Search input with debouncing
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300); // 300ms delay
            });

            // Status select immediate search
            statusSelect.addEventListener('change', function() {
                performSearch();
            });

            // Show loading indicator
            function showLoading() {
                websitesContainer.innerHTML = '<div class="text-center py-12"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div><p class="mt-2 text-gray-500">Searching...</p></div>';
            }

            // Override form submission to use AJAX
            const filterForm = document.querySelector('form[method="GET"]');
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                performSearch();
            });
        });
    </script>
</x-app-layout>
