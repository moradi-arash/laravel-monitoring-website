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
