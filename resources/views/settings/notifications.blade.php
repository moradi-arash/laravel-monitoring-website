<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Notification Preferences') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            Configure Telegram Notifications
                        </h3>
                        <p class="text-sm text-gray-600">
                            Choose which types of errors you want to receive notifications for via Telegram.
                            Note: All errors will still be displayed in the dashboard regardless of these settings.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('settings.notifications.update') }}" id="notification-form">
                        @csrf
                        @method('PUT')

                        <!-- Individual Error Types -->
                        <div class="space-y-4">
                            @foreach($errorTypeLabels as $type => $label)
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input
                                            id="notify_{{ $type }}"
                                            name="notify_{{ $type }}"
                                            type="checkbox"
                                            value="1"
                                            {{ $settings->{'notify_' . $type} ? 'checked' : '' }}
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                        />
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="notify_{{ $type }}" class="font-medium text-gray-700">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Save Button -->
                        <div class="mt-8 flex items-center justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Box -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">
                            Important Information
                        </h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>These settings only affect Telegram notifications</li>
                                <li>All errors are always visible in your dashboard</li>
                                <li>Make sure your Telegram credentials are configured in <a href="{{ route('settings.edit') }}" class="font-medium underline">Settings</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>