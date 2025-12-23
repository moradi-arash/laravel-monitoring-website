<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    User Details: {{ $user->name }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">View and manage user information</p>
            </div>
            <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                ← Back to Users
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- User Information Card -->
            <div class="bg-white shadow sm:rounded-lg p-4 sm:p-8">
                <div class="max-w-2xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
                    
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Role</dt>
                            <dd class="mt-1">
                                @if($user->role === 'admin')
                                    <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                                        Admin
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">
                                        User
                                    </span>
                                @endif
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Account Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y H:i') }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('M d, Y H:i') }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($user->email_verified_at)
                                    <span class="text-green-600">✓ Verified</span>
                                @else
                                    <span class="text-red-600">✗ Not Verified</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Telegram Settings Card -->
            <div class="bg-white shadow sm:rounded-lg p-4 sm:p-8">
                <div class="max-w-2xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Telegram Settings</h3>
                    
                    @if($user->settings)
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Telegram Configured</dt>
                                <dd class="mt-1 text-sm text-green-600">✓ Yes</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Bot Token</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $user->settings->telegram_bot_token ? substr($user->settings->telegram_bot_token, 0, 10) . '...' : 'Not set' }}
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Chat ID</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $user->settings->telegram_chat_id ?: 'Not set' }}
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->settings->updated_at->format('M d, Y H:i') }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-sm text-gray-500">No Telegram settings configured.</p>
                    @endif
                </div>
            </div>

            <!-- User's Websites Card -->
            <div class="bg-white shadow sm:rounded-lg p-4 sm:p-8">
                <div class="max-w-4xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">User's Websites ({{ $user->websites->count() }})</h3>
                    
                    @if($user->websites->count() > 0)
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Checked</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($user->websites as $website)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $website->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <a href="{{ $website->url }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                    {{ $website->url }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($website->is_active)
                                                    <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $website->last_checked_at ? $website->last_checked_at->format('M d, Y H:i') : 'Never' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No websites configured.</p>
                    @endif
                </div>
            </div>

            <!-- Role Management Section -->
            @if($user->id !== auth()->id())
                <div class="bg-white shadow sm:rounded-lg p-4 sm:p-8">
                    <div class="max-w-2xl">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Role Management</h3>
                        
                        <form method="POST" action="{{ route('users.update-role', $user) }}">
                            @csrf
                            @method('PATCH')
                            
                            <div class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700">Current Role</label>
                                    <select name="role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </div>
                                
                                <div class="flex items-end">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Update Role
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Self-Management Notice</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>You cannot change your own role. This prevents accidental self-demotion and ensures at least one admin always exists.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Danger Zone -->
            @if($user->id !== auth()->id())
                <div class="bg-white shadow sm:rounded-lg p-4 sm:p-8 border-2 border-red-200">
                    <div class="max-w-2xl">
                        <h3 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Deleting this user will permanently remove all associated data including websites, settings, and monitoring history. This action cannot be undone.
                        </p>
                        
                        <x-danger-button
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                        >
                            Delete User Account
                        </x-danger-button>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <x-modal name="confirm-user-deletion" focusable>
                    <form method="post" action="{{ route('users.destroy', $user) }}" class="p-6">
                        @csrf
                        @method('delete')

                        <h2 class="text-lg font-medium text-gray-900">
                            Are you sure you want to delete this user?
                        </h2>

                        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
                            <p class="text-sm font-medium text-red-800">User Information:</p>
                            <ul class="mt-2 text-sm text-red-700 space-y-1">
                                <li><strong>Name:</strong> {{ $user->name }}</li>
                                <li><strong>Email:</strong> {{ $user->email }}</li>
                                <li><strong>Role:</strong> {{ ucfirst($user->role) }}</li>
                            </ul>
                        </div>

                        <p class="mt-4 text-sm text-gray-600">
                            This will permanently delete all associated data:
                        </p>

                        <ul class="mt-2 text-sm text-gray-600 list-disc list-inside space-y-1">
                            <li><strong>{{ $user->websites->count() }} website(s)</strong> and their monitoring history</li>
                            <li>Telegram notification settings</li>
                            <li>All user preferences and data</li>
                        </ul>

                        <div class="mt-6">
                            <x-input-label for="confirm_name" value="Type the user's name to confirm deletion" class="font-semibold" />
                            <x-text-input
                                id="confirm_name"
                                name="confirm_name"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="{{ $user->name }}"
                                required
                                autofocus
                            />
                            <p class="mt-1 text-xs text-gray-500">Please type: <span class="font-mono font-semibold">{{ $user->name }}</span></p>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-secondary-button x-on:click="$dispatch('close')">
                                Cancel
                            </x-secondary-button>

                            <x-danger-button class="ms-3">
                                Delete User Permanently
                            </x-danger-button>
                        </div>
                    </form>
                </x-modal>
            @endif
        </div>
    </div>
</x-app-layout>
