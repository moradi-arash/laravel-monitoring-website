<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Cron Security Settings</h2>
        <p class="mt-1 text-sm text-gray-600">Configure IP whitelist for cron job access security.</p>
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
                         placeholder="127.0.0.1,192.168.1.100,::1,2001:db8::1" />
            <x-input-error class="mt-2" :messages="$errors->get('cron_allowed_ip')" />
            <div class="mt-2 text-sm text-gray-600 space-y-2">
                <p><strong>Required for both CLI and HTTP access:</strong></p>
                <ul class="list-disc list-inside ml-4 space-y-1">
                    <li><strong>CLI Mode:</strong> Include your server's IP addresses (localhost, private IPs, public IPs)</li>
                    <li><strong>HTTP Mode:</strong> Include the requesting client's IP address</li>
                </ul>
                <p><strong>Examples:</strong></p>
                <ul class="list-disc list-inside ml-4 space-y-1">
                    <li>Single IP: <code class="bg-gray-100 px-1 rounded">127.0.0.1</code></li>
                    <li>Multiple IPv4: <code class="bg-gray-100 px-1 rounded">127.0.0.1,192.168.1.100,10.0.0.5</code></li>
                    <li>Mixed IPv4/IPv6: <code class="bg-gray-100 px-1 rounded">127.0.0.1,::1,192.168.1.100,2001:db8::1</code></li>
                    <li>Server IPs for CLI: <code class="bg-gray-100 px-1 rounded">127.0.0.1,::1,192.168.1.100</code></li>
                </ul>
                <p class="text-yellow-700 font-medium">⚠️ <strong>Important:</strong> For CLI cron jobs, you must include your server's actual IP addresses, not just localhost!</p>
            </div>
        </div>

        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Security Requirements</h3>
                    <div class="mt-2 text-sm text-yellow-700 space-y-2">
                        <p><strong>These settings control access to your cron monitoring endpoints for BOTH CLI and HTTP access:</strong></p>
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>IP Whitelist:</strong> Required for both CLI and HTTP requests</li>
                            <li><strong>CLI Access:</strong> Must include your server's actual IP addresses</li>
                            <li><strong>HTTP Access:</strong> Must include the requesting client's IP address</li>
                            <li><strong>Testing:</strong> Always test your cron jobs after making changes</li>
                        </ul>
                        <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded">
                            <p class="font-medium text-red-800">🚨 Critical Security Note:</p>
                            <p class="text-red-700">If you only include localhost (127.0.0.1) and your server has multiple IP addresses, CLI cron jobs may fail. Include all relevant server IPs!</p>
                        </div>
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
