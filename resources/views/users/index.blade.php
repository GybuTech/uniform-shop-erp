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
                    <td class="px-6 py-4 text-sm text-right space-x-2 whitespace-nowrap">
                        <a href="{{ route('users.edit', $user) }}" title="Edit"
                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline" onsubmit="return confirm('Change account status for {{ $user->name }}?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $user->is_active ? 'bg-amber-50 hover:bg-amber-100 text-amber-600' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600' }} transition">
                                @if($user->is_active)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728M12 8v4l3 3"/></svg>
                                @endif
                            </button>
                        </form>
                        @can('delete-users')
                            @if(auth()->id() !== $user->id && !in_array('owner', $user->roles->pluck('name')->toArray()))
                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('WARNING: Permanently delete {{ $user->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">No staff accounts found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
