<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Website: ') . $website->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('websites.update', $website) }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="Website Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $website->name)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        <p class="mt-1 text-sm text-gray-600">A friendly name to identify this website</p>
                    </div>

                    <div>
                        <x-input-label for="url" value="Website URL" />
                        <x-text-input id="url" name="url" type="url" class="mt-1 block w-full" :value="old('url', $website->url)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('url')" />
                        <p class="mt-1 text-sm text-gray-600">The full URL to monitor (must include http:// or https://)</p>
                    </div>

                    <div>
                        <x-input-label for="is_active" value="Monitoring Status" />
                        <div class="mt-2">
                            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $website->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="ms-2 text-sm text-gray-600">Enable monitoring for this website</span>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                    </div>

                    <div class="p-4 bg-gray-50 rounded-md border border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Monitoring Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Last Checked:</span>
                                <span class="text-gray-900">
                                    {{ $website->last_checked_at ? $website->last_checked_at->format('M d, Y H:i:s') : 'Never' }}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Last Status Code:</span>
                                <span class="text-gray-900">{{ $website->last_status_code ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Last Error:</span>
                                <span class="text-gray-900">
                                    {{ $website->last_error ? Str::limit($website->last_error, 50) : 'None' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Update Website') }}</x-primary-button>
                        <a href="{{ route('websites.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>



