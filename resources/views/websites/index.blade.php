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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @forelse($websites as $website)
                        <div class="mb-6 last:mb-0">
                            <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-medium text-gray-900">{{ $website->name }}</h3>
                                            <p class="text-sm text-gray-600">
                                                <a href="{{ $website->url }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                    {{ $website->url }}
                                                </a>
                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            @if($website->is_active)
                                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                                    Active
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">
                                                    Inactive
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="font-medium text-gray-700">Last Checked:</span>
                                            <span class="text-gray-900">
                                                {{ $website->last_checked_at ? $website->last_checked_at->format('M d, Y H:i') : 'Never' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Status Code:</span>
                                            <span class="text-gray-900">
                                                @if($website->last_status_code)
                                                    @if($website->last_status_code >= 200 && $website->last_status_code < 300)
                                                        <span class="text-green-600">{{ $website->last_status_code }}</span>
                                                    @elseif($website->last_status_code >= 400 && $website->last_status_code < 500)
                                                        <span class="text-yellow-600">{{ $website->last_status_code }}</span>
                                                    @else
                                                        <span class="text-red-600">{{ $website->last_status_code }}</span>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Error:</span>
                                            <span class="text-gray-900">
                                                @if($website->last_error)
                                                    <span title="{{ $website->last_error }}">
                                                        {{ Str::limit($website->last_error, 50) }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4">
                                    <a href="{{ route('websites.edit', $website) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('websites.destroy', $website) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this website?')" class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">No websites configured yet.</p>
                            <p class="text-gray-400 mt-2">Add your first website to start monitoring.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
