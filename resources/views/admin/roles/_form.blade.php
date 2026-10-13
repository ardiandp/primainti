<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Role</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $role->name ?? '') }}"
               placeholder="mis. content_admin"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
        <input id="description" name="description" type="text" value="{{ old('description', $role->description ?? '') }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
    </div>

    <div>
        <span class="block text-sm font-medium text-gray-700 mb-2">Permissions</span>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach ($permissions as $permission)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="rounded border-gray-300"
                           @checked(in_array($permission->id, old('permissions', $role?->permissions?->pluck('id')->all() ?? [])))>
                    <code class="text-xs bg-gray-100 rounded px-1.5 py-0.5">{{ $permission->name }}</code>
                </label>
            @endforeach
        </div>
    </div>
</div>
