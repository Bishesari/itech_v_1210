<?php
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new class extends Component {
    #[Locked]
    public User $user;

    public Collection $roles;

};
?>

<div>
    <div class="bg-zinc-100 dark:bg-zinc-600 dark:text-zinc-300 py-3 relative">
        <p class="font-semibold text-center">{{__('جزئیات کاربر:')}} ( {{$user['user_name']}} )</p>
        <section class="absolute left-1 top-2">
            <flux:button href="{{route('users')}}" variant="ghost" size="sm" class="cursor-pointer" wire:navigate>
                <flux:icon.arrow-up-circle class="text-blue-500 size-6"/>
            </flux:button>
        </section>
    </div>

</div>
