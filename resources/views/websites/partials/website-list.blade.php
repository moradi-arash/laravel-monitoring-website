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
        <p class="text-gray-500 text-lg">No websites found.</p>
        <p class="text-gray-400 mt-2">Try adjusting your search criteria.</p>
    </div>
@endforelse
