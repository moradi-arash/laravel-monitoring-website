<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Management') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">Manage user accounts and roles</p>
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

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $users->total() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Admin Users</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $adminCount }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Regular Users</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $regularCount }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @forelse($users as $user)
                        <div class="mb-6 last:mb-0">
                            <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                                            <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            @if($user->role === 'admin')
                                                <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                                                    Admin
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">
                                                    User
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="font-medium text-gray-700">Websites:</span>
                                            <span class="text-gray-900">{{ $user->websites_count }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Joined:</span>
                                            <span class="text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Last Updated:</span>
                                            <span class="text-gray-900">{{ $user->updated_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4">
                                    <a href="{{ route('users.show', $user) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                        View Details
                                    </a>
                                    
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.update-role', $user) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                            </select>
                                        </form>
                                        
                                        <button
                                            type="button"
                                            x-data=""
                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion-{{ $user->id }}')"
                                            class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >
                                            Delete
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-500">Self</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Delete Confirmation Modal -->
                        @if($user->id !== auth()->id())
                            <x-modal name="confirm-user-deletion-{{ $user->id }}" focusable>
                                <form method="post" action="{{ route('users.destroy', $user) }}" class="p-6">
                                    @csrf
                                    @method('delete')

                                    <h2 class="text-lg font-medium text-gray-900">
                                        Are you sure you want to delete this user?
                                    </h2>

                                    <p class="mt-1 text-sm text-gray-600">
                                        This will permanently delete <strong>{{ $user->name }}</strong> ({{ $user->email }}) and all associated data including:
                                    </p>

                                    <ul class="mt-2 text-sm text-gray-600 list-disc list-inside">
                                        <li>{{ $user->websites_count }} website(s)</li>
                                        <li>Telegram settings</li>
                                        <li>All monitoring history</li>
                                    </ul>

                                    <div class="mt-6">
                                        <x-input-label for="confirm_name_{{ $user->id }}" value="Type the user's name to confirm" />
                                        <x-text-input
                                            id="confirm_name_{{ $user->id }}"
                                            name="confirm_name"
                                            type="text"
                                            class="mt-1 block w-3/4"
                                            placeholder="{{ $user->name }}"
                                            required
                                        />
                                        <p class="mt-1 text-xs text-gray-500">Type: {{ $user->name }}</p>
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
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">No users found.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
