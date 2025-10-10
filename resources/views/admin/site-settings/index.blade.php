<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Website Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'settings-updated')
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    Website settings updated successfully!
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.site-settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <!-- Site Name -->
                        <div>
                            <label for="site_name" class="block text-sm font-medium text-gray-700">
                                Website Name
                            </label>
                            <input type="text" 
                                   name="site_name" 
                                   id="site_name"
                                   value="{{ old('site_name', $settings->site_name) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="Enter your website name">
                            @error('site_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Logo Upload -->
                        <div>
                            <label for="logo" class="block text-sm font-medium text-gray-700">
                                Website Logo
                            </label>
                            <div class="mt-1">
                                @if($settings->logo_path)
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-600 mb-2">Current logo:</p>
                                        <img src="{{ asset('storage/logos/' . $settings->logo_path) }}" 
                                             alt="Current Logo" 
                                             class="h-20 w-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @endif
                                
                                <input type="file" 
                                       name="logo" 
                                       id="logo"
                                       accept="image/jpeg,image/png,image/jpg,image/webp"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                
                                <p class="mt-2 text-sm text-gray-500">
                                    Allowed formats: JPEG, PNG, JPG, WebP | Maximum size: 2MB | Image must be square (1:1 aspect ratio)
                                </p>
                            </div>
                            @error('logo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
