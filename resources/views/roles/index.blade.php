<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Role & Permission Matrix
            </h2>
            <a href="{{ route('users.index') }}" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-700">
                &larr; Manage User Accounts
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white p-5 rounded-xl shadow-sm border mb-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">System Access Roles</h3>
                        <p class="text-xs text-gray-500 mt-1">Configure role permissions. Check or uncheck permissions to grant or limit what staff members can perform in the ERP.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($roles as $role)
                <div class="bg-white border rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-extrabold text-base text-gray-900 capitalize">{{ str_replace('_', ' ', $role->name) }}</h4>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase">
                                    {{ $role->users_count }} Assigned User(s)
                                </span>
                            </div>
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                {{ $role->permissions_count }} Active Permissions
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 line-clamp-2">{{ $role->description ?? 'No description provided.' }}</p>
                    </div>

                    <div class="bg-gray-50 px-5 py-3 border-t flex justify-between items-center">
                        <a href="{{ route('roles.edit', $role) }}" class="text-xs font-extrabold text-indigo-600 hover:text-indigo-900 flex items-center">
                            Edit Permission Checkboxes &rarr;
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
