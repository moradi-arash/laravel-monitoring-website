<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Website') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('websites.store') }}" class="p-6 space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Website Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        <p class="mt-1 text-sm text-gray-600">A friendly name to identify this website (e.g., "Company Homepage")</p>
                    </div>

                    <div>
                        <x-input-label for="url" value="Website URL" />
                        <x-text-input id="url" name="url" type="url" class="mt-1 block w-full" :value="old('url')" required placeholder="https://example.com" />
                        <x-input-error class="mt-2" :messages="$errors->get('url')" />
                        <p class="mt-1 text-sm text-gray-600">The full URL to monitor (must include http:// or https://)</p>
                    </div>

                    <div>
                        <x-input-label for="is_active" value="Monitoring Status" />
                        <div class="mt-2">
                            <input type="checkbox" id="is_active" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="ms-2 text-sm text-gray-600">Enable monitoring for this website</span>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Add Website') }}</x-primary-button>
                        <a href="{{ route('websites.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>



