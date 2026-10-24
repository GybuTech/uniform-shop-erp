<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Role Permissions Matrix — {{ ucfirst(str_replace('_', ' ', $role->name)) }}
            </h2>
            <a href="{{ route('roles.index') }}" class="text-sm font-bold text-gray-600 hover:underline">
                &larr; Back to Roles
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-6 rounded-xl shadow-sm border space-y-4">
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">Configure Access Rights for {{ ucfirst(str_replace('_', ' ', $role->name)) }}</h3>
                    <p class="text-xs text-gray-500">Tick or untick checkboxes to grant or restrict what users assigned to this role can access or manipulate in the system.</p>
                </div>

                <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Role Description</label>
                        <input type="text" name="description" value="{{ old('description', $role->description) }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div class="border-t pt-4 space-y-6">
                        @foreach($groupedPermissions as $groupName => $permissionList)
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <div class="flex justify-between items-center mb-3 pb-2 border-b">
                                <h4 class="font-extrabold text-sm text-gray-800 flex items-center">
                                    <span class="mr-2">📁</span> {{ $groupName }}
                                </h4>
                                <label class="text-xs font-semibold text-indigo-600 cursor-pointer hover:underline select-none">
                                    <input type="checkbox" onchange="toggleGroup(this, 'group-{{ Str::slug($groupName) }}')" class="rounded border-gray-300 text-indigo-600">
                                    <span>Select All in {{ $groupName }}</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 group-{{ Str::slug($groupName) }}">
                                @foreach($permissionList as $perm)
                                <label class="flex items-center space-x-2.5 p-2 bg-white rounded-lg border hover:bg-indigo-50/50 cursor-pointer text-xs font-semibold select-none">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm }}"
                                        {{ in_array($perm, $rolePermissions) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="capitalize text-gray-700">{{ str_replace('-', ' ', $perm) }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t">
                        <a href="{{ route('roles.index') }}" class="text-xs font-bold text-gray-500 hover:underline">Cancel</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg text-sm font-extrabold shadow-sm transition">
                            💾 Save Permission Settings
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        function toggleGroup(masterCheckbox, groupClass) {
            const checkboxes = document.querySelectorAll('.' + groupClass + ' input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
        }
    </script>
</x-app-layout>
