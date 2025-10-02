<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Cron Security Settings</h2>
        <p class="mt-1 text-sm text-gray-600">Configure IP whitelist and secret key for cron job access security.</p>
    </header>

    <form method="post" action="{{ route('settings.cron.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="cron_allowed_ip" value="Cron Allowed IP" />
            <x-text-input id="cron_allowed_ip" 
                         name="cron_allowed_ip" 
                         type="text" 
                         class="mt-1 block w-full" 
                         value="{{ old('cron_allowed_ip', $globalConfig['cron_allowed_ip'] ?? '') }}" 
                         placeholder="127.0.0.1,192.168.1.100" />
            <x-input-error class="mt-2" :messages="$errors->get('cron_allowed_ip')" />
            <p class="mt-1 text-sm text-gray-600">Comma-separated list of IP addresses allowed to access cron endpoints</p>
        </div>

        <div>
            <x-input-label for="cron_secret_key" value="Cron Secret Key" />
            <x-text-input id="cron_secret_key" 
                         name="cron_secret_key" 
                         type="password" 
                         class="mt-1 block w-full" 
                         value="{{ old('cron_secret_key', $globalConfig['cron_secret_key'] ?? '') }}" 
                         placeholder="Enter a strong secret key" />
            <x-input-error class="mt-2" :messages="$errors->get('cron_secret_key')" />
            <p class="mt-1 text-sm text-gray-600">Secret key required for cron job authentication (minimum 8 characters)</p>
        </div>

        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Security Warning</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>These settings control access to your cron monitoring endpoints. Make sure to:</p>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li>Use a strong, unique secret key</li>
                            <li>Only allow trusted IP addresses</li>
                            <li>Test your cron jobs after making changes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Cron Settings') }}</x-primary-button>

            @if (session('status') === 'cron-settings-updated')
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
