<flux:footer
    class="bg-zinc-50 grid grid-cols-1 md:grid-cols-2 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700">
    @php
        // نام Route جاری
        $currentRoute = Illuminate\Support\Facades\Route::currentRouteName() ?? request()->path();

        // ثبت بازدید
        \App\Helpers\PageVisitHelper::register($currentRoute);

        // تعداد بازدید صفحه
        $pageVisits = \App\Helpers\PageVisitHelper::count($currentRoute);
    @endphp

    <div class="text-center">
        <a href="{{ route('home') }}" class="flex flex-col items-center" wire:navigate>
            <x-logo class="text-zinc-700 dark:text-zinc-300 h-12"/>
        </a>

        <flux:text class="pt-2 tracking-tight">
            {{__('موسس: بخشی زاده')}}
        </flux:text>

        <flux:text class="pt-2 tracking-tight">
            {{__('مشاوره: 8163 056 935 98+')}}
        </flux:text>

        <flux:text class="pt-2 tracking-tight">
            {{__('تلفن تماس: 50 38 54 33 77 98+')}}
        </flux:text>

        <flux:text class="pt-2 tracking-tight">
            {{__('بوشهر، خیابان سنگی، اول گلخونه، سیراف 5')}}
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

        <flux:text class="pt-3">
            {{__('تماس: 6111 433 903 98+')}}
            {{__(' و ')}}
            {{__('Yasser.Bishesari@Gmail.Com')}}
        </flux:text>

        <flux:text class="pt-3">
            {{__('S.V: 12.1.5 - L.V:')}}
            {{__(Illuminate\Foundation\Application::VERSION)}}
            {{__(' - PHP.V: '.PHP_VERSION)}}
            {{__(' - P.V: '.$pageVisits)}}
        </flux:text>

        <flux:text class="pt-3">
            {{__('برنامه نویسی و اجرا: بیشه سری')}}
        </flux:text>

    </div>
</flux:footer>
