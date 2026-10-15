<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Staff & User Management') }}
            </h2>
            <a href="{{ route('users.create') }}"
               class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium text-sm transition">
                + Add Staff Member
            </a>
        </div>
    </x-slot>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Assigned Role</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Assigned Branch</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Account Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                    <td class="px-6 py-4 text-sm font-mono font-bold text-indigo-600">{{ $user->username ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4 text-sm">
                        @forelse($user->roles as $role)
                            <span class="inline-block bg-indigo-100 text-indigo-800 text-xs px-2.5 py-0.5 rounded font-semibold capitalize">
                                {{ $role->name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400 italic">No role</span>
                        @endforelse
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($user->branch_name)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $user->branch_name }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                All Branches (HQ)
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($user->is_active)
                            <span class="inline-block bg-emerald-100 text-emerald-800 text-xs px-2.5 py-0.5 rounded font-semibold">Active</span>
                        @else
                            <span class="inline-block bg-red-100 text-red-800 text-xs px-2.5 py-0.5 rounded font-semibold">Deactivated</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-right space-x-2">
                        <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                        <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline" onsubmit="return confirm('Change account status for {{ $user->name }}?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="{{ $user->is_active ? 'text-amber-600 hover:text-amber-900' : 'text-emerald-600 hover:text-emerald-900' }} font-medium">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        @can('delete-users')
                            @if(auth()->id() !== $user->id && !in_array('owner', $user->roles->pluck('name')->toArray()))
                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete staff account {{ $user->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium hover:underline ml-1" style="color: #dc2626 !important;">
                                    Delete
                                </button>
                            </form>
                            @endif
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No staff accounts found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
