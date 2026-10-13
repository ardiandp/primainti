<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $user->name ?? '') }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input id="email" name="email" type="email" required value="{{ old('email', $user->email ?? '') }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Password {{ isset($user) ? '(kosongkan bila tidak diubah)' : '' }}
        </label>
        <input id="password" name="password" type="password" {{ isset($user) ? '' : 'required' }}
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" {{ isset($user) ? '' : 'required' }}
               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#F97316]">
    </div>

    <div>
        <span class="block text-sm font-medium text-gray-700 mb-2">Role</span>
        <div class="space-y-2">
            @foreach ($roles as $role)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="rounded border-gray-300"
                           @checked(in_array($role->id, old('roles', $user?->roles?->pluck('id')->all() ?? [])))>
                    {{ $role->name }}
                    <span class="text-xs text-gray-400">— {{ $role->description }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>
