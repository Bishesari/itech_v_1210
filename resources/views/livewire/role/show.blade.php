<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new class extends Component {
    #[Locked]
    public Role $role;

    public Collection $permissions;

    public array $rolePermissionsIds;

    public function mount()
    {
        $this->get_permissions();
        $this->get_role_permissions_ids();

    }

    public function get_permissions(): void
    {
        $this->permissions = Permission::all();
    }

    public function get_role_permissions_ids(): void
    {
        $this->rolePermissionsIds =  $this->role->permissions->pluck('id')->toArray();
    }

    public function togglePermission($permissionId): void
    {
        $permission = Permission::findOrFail($permissionId);
        if (in_array($permissionId, $this->rolePermissionsIds)) {
            $this->role->permissions()->detach($permission);
        } else {
            $this->role->permissions()->attach($permission, [
                'created' => j_d_stamp_en(),
            ]);
        }
        $this->get_role_permissions_ids();
    }




}; ?>

<div>
    <div class="bg-zinc-100 dark:bg-zinc-600 dark:text-zinc-300 py-3 relative">
        <p class="font-semibold text-center">{{__('جزئیات نقش کاربری:')}} ( {{$role['name_fa']}}، {{$role['name_en']}}
            )</p>
        <section class="absolute left-1 top-2">
            <flux:button href="{{route('roles')}}" variant="ghost" size="sm" class="cursor-pointer" wire:navigate>
                <flux:icon.arrow-up-circle class="text-blue-500 size-6"/>
            </flux:button>
        </section>

    </div>

    <flux:checkbox.group wire:model="Subscription" label="Subscription preferences" variant="cards" class="max-sm:flex-col">
        @foreach($permissions as $permission)

            @if(in_array($permission->id, $rolePermissionsIds))
                <flux:checkbox wire:click="togglePermission({{$permission->id}})" class="min-w-36 m-2"
                    value="{{$permission->id}}"
                    label="{{$permission->name_fa}}"
                    description="Learn about new features and products."
                    checked
                />
            @else
                <flux:checkbox wire:click="togglePermission({{$permission->id}})" class="min-w-36 m-2"
                    value="{{$permission->id}}"
                    label="{{$permission->name_fa}}"
                    description="Learn about new features and products."
                />
            @endif

        @endforeach
    </flux:checkbox.group>

</div>
