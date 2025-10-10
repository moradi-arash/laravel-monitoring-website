<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Telegram Bot Configuration</h2>
        <p class="mt-1 text-sm text-gray-600">Enter your Telegram bot token and chat ID to receive monitoring alerts. Leave empty to disable notifications.</p>
    </header>

    <form method="post" action="{{ route('settings.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="telegram_bot_token" value="Telegram Bot Token" />
            <x-text-input id="telegram_bot_token" 
                         name="telegram_bot_token" 
                         type="text" 
                         class="mt-1 block w-full" 
                         value="{{ old('telegram_bot_token', $settings->telegram_bot_token ?? '') }}" 
                         placeholder="1234567890:ABCdefGHIjklMNOpqrsTUVwxyz" />
            <x-input-error class="mt-2" :messages="$errors->get('telegram_bot_token')" />
            <p class="mt-1 text-sm text-gray-600">Get your bot token from @BotFather on Telegram</p>
        </div>

        <div>
            <x-input-label for="telegram_chat_id" value="Telegram Chat ID" />
            <x-text-input id="telegram_chat_id" 
                         name="telegram_chat_id" 
                         type="text" 
                         class="mt-1 block w-full" 
                         value="{{ old('telegram_chat_id', $settings->telegram_chat_id ?? '') }}" 
                         placeholder="123456789" />
            <x-input-error class="mt-2" :messages="$errors->get('telegram_chat_id')" />
            <p class="mt-1 text-sm text-gray-600">Your personal chat ID or group chat ID</p>
        </div>

        <div class="p-4 bg-blue-50 border border-blue-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Setup Instructions</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>How to get your Chat ID:</strong> Start a chat with @userinfobot on Telegram</li>
                            <li><strong>How to create a bot:</strong> Message @BotFather and use /newbot command</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Settings') }}</x-primary-button>

            @if (session('status') === 'settings-updated')
                <p x-data="{ show: true }" 
                   x-show="show" 
                   x-transition 
                   x-init="setTimeout(() => show = false, 2000)" 
                   class="text-sm text-gray-600">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>



