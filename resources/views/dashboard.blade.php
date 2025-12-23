<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Websites Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Websites</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalWebsites ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Websites Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Websites</p>
                                <p class="text-2xl font-semibold text-green-600">{{ $activeWebsites ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inactive Websites Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Inactive Websites</p>
                                <p class="text-2xl font-semibold text-red-600">{{ $inactiveWebsites ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Check Countdown Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Next Automatic Check</p>
                                <p class="text-lg font-semibold text-purple-600" id="countdown-timer">
                                    Loading...
                                </p>
                            </div>
                        </div>
                        @if($executionMethod && auth()->user()->isAdmin())
                            <div class="mt-2 pt-2 border-t border-gray-100">
                                <div class="flex items-center text-xs">
                                    <span class="text-gray-500">Last run via:</span>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $executionMethod === 'artisan' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $executionMethod === 'standalone' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $executionMethod === 'unknown' ? 'bg-gray-100 text-gray-800' : '' }}
                                    ">
                                        @if($executionMethod === 'artisan')
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                            </svg>
                                            Artisan
                                        @elseif($executionMethod === 'standalone')
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                            Standalone PHP
                                        @else
                                            {{ $executionMethodLabel }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Errors Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Recent Errors</h3>
                        @if($totalErrors > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ $totalErrors }} {{ $totalErrors === 1 ? 'Error' : 'Errors' }}
                            </span>
                        @endif
                    </div>
                    
                    @if($websitesWithErrors && $websitesWithErrors->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Website</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Error Message</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Checked</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($websitesWithErrors as $website)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $website->name ?? 'Unnamed' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <a href="{{ $website->url }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                    {{ Str::limit($website->url, 50) }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <span class="text-red-600">{{ Str::limit($website->last_error, 100) }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $website->last_checked_at ? $website->last_checked_at->format('M j, Y g:i A') : 'Never' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No errors found</h3>
                            <p class="mt-1 text-sm text-gray-500">All your websites are running smoothly!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Countdown Timer JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countdownElement = document.getElementById('countdown-timer');
            
            if (!countdownElement) {
                console.log('Countdown element not found');
                return;
            }
            
            try {
                // Get the next check time from server
                const nextCheckTimeString = '{{ $nextCheckTime->toISOString() }}';
                const checkIntervalMinutes = {{ $checkInterval ?? 10 }};
                
                console.log('Next check time:', nextCheckTimeString);
                console.log('Check interval:', checkIntervalMinutes);
                
                const nextCheckTime = new Date(nextCheckTimeString);
                
                // Validate the date
                if (isNaN(nextCheckTime.getTime())) {
                    throw new Error('Invalid date format');
                }
                
                function updateCountdown() {
                    try {
                        const now = new Date();
                        const timeDiff = nextCheckTime.getTime() - now.getTime();
                        
                        if (timeDiff <= 0) {
                            // Check is overdue or happening now
                            countdownElement.textContent = 'Checking now...';
                            countdownElement.className = 'text-lg font-semibold text-green-600';
                        } else {
                            // Calculate remaining time
                            const totalSeconds = Math.floor(timeDiff / 1000);
                            const minutes = Math.floor(totalSeconds / 60);
                            const seconds = totalSeconds % 60;
                            
                            if (minutes > 0) {
                                countdownElement.textContent = `${minutes}m ${seconds}s`;
                            } else {
                                countdownElement.textContent = `${seconds}s`;
                            }
                            countdownElement.className = 'text-lg font-semibold text-purple-600';
                        }
                    } catch (error) {
                        console.error('Error in updateCountdown:', error);
                        countdownElement.textContent = 'Error';
                        countdownElement.className = 'text-lg font-semibold text-red-600';
                    }
                }
                
                // Update immediately
                updateCountdown();
                
                // Update every second
                setInterval(updateCountdown, 1000);
                
            } catch (error) {
                console.error('Error initializing countdown:', error);
                // Fallback if there's any JavaScript error
                countdownElement.textContent = '10m 0s';
                countdownElement.className = 'text-lg font-semibold text-purple-600';
            }
        });
    </script>
</x-app-layout>
