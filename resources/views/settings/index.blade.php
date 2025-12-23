<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'settings-updated')
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">Settings updated successfully.</span>
                </div>
            @endif

            @if (session('status') === 'cron-settings-updated')
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">Cron settings updated successfully.</span>
                </div>
            @endif

            <!-- User Telegram Settings Section -->
            <div class="bg-white shadow sm:rounded-lg p-4 sm:p-8">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900">Telegram Notification Settings</h3>
                    <p class="mt-1 text-sm text-gray-600">Configure your Telegram bot credentials to receive website monitoring alerts.</p>
                    
                    @include('settings.partials.update-telegram-settings-form')
                    
                    <!-- Notification Preferences Link -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h4 class="text-sm font-medium text-gray-900">
                                    Notification Preferences
                                </h4>
                                <p class="mt-1 text-sm text-gray-600">
                                    Customize which types of errors you want to receive notifications for.
                                </p>
                                <div class="mt-3">
                                    <a href="{{ route('settings.notifications') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Configure Notification Preferences
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($globalConfig)
            <!-- Global Cron Configuration Section -->
            <div class="bg-white shadow sm:rounded-lg p-4 sm:p-8">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900">System Configuration</h3>
                    <p class="mt-1 text-sm text-gray-600">Configure system-wide security settings for cron job access.</p>
                    
                    @include('settings.partials.update-cron-settings-form')
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
