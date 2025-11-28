<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    public string $email = '';
    public string $password = '';
    public string $mode = 'login'; // login | register

    public function login()
    {
        $this->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['email'=>$this->email, 'password'=>$this->password])) {
            session()->regenerate();
            $this->dispatch('close-modal', name: 'auth-modal');
            $this->dispatch('auth-updated'); // هدر رفرش می‌شود
        } else {
            $this->addError('email', 'اطلاعات ورود صحیح نیست.');
        }
    }

    public function register()
    {
        $this->validate([
            'email' => ['required','email','unique:users,email'],
            'password' => ['required','min:6'],
        ]);

        $user = \App\Models\User::create([
            'name' => 'Yasser',
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        Auth::login($user);
        session()->regenerate();

        $this->dispatch('close-modal', name: 'auth-modal');
        $this->dispatch('$refresh');
    }
};
?>


<flux:modal name="auth-modal" persistent>
    <div class="p-6 space-y-4">

        <h2 class="text-xl font-semibold text-center">
            {{ $mode === 'login' ? 'ورود' : 'ثبت‌نام' }}
        </h2>

        <flux:input wire:model="email" label="ایمیل" />
        <flux:input type="password" wire:model="password" label="رمز عبور" />

        @if($mode === 'login')

            <flux:button wire:click="login" variant="primary" class="w-full">
                ورود
            </flux:button>

            <p class="text-center text-sm mt-2">
                حساب ندارید؟
                <span wire:click="$set('mode','register')" class="text-blue-600 cursor-pointer">
                    ثبت‌نام
                </span>
            </p>

        @else

            <flux:button wire:click="register" variant="primary" class="w-full">
                ثبت‌نام
            </flux:button>

            <p class="text-center text-sm mt-2">
                حساب دارید؟
                <span wire:click="$set('mode','login')" class="text-blue-600 cursor-pointer">
                    ورود
                </span>
            </p>

        @endif

    </div>
</flux:modal>
