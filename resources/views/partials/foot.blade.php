    <div class="text-center">
        <a href="{{ route('home') }}" class="flex flex-col items-center" wire:navigate>
            <x-logo class="text-zinc-700 dark:text-zinc-300 h-12"/>
        </a>

        <flux:text class="pt-2 tracking-tight">
            {{__('موسس: بخشی زاده')}}
        </flux:text>

        <flux:text class="pt-2 tracking-tight">
        </flux:text>
        <flux:text class="pt-2 tracking-tight">
        </flux:text>
    </div>

    <flux:separator class="block md:hidden my-5"/>

    <div class="text-center">
        <flux:text class="pt-2">
            <span class="font-semibold">&copy;</span>
            {{__('تمامی حقوق برای آموزشگاه آی تک محفوظ است.')}}
            {{__('از 1388 تا')}}
            {{jdate('Y', time(), '', '', 'en')}}
        </flux:text>

            {{__('تماس: 6111 433 903 98+')}}
            {{__(' و ')}}
            {{__('Yasser.Bishesari@Gmail.Com')}}
        </flux:text>

            {{__(Illuminate\Foundation\Application::VERSION)}}
        </flux:text>
    </div>
</flux:footer>
