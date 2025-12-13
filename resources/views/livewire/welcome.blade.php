<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;


new
#[Layout('components.layouts.public')]
#[Title('آموزشگاه کامپیوتر، حسابداری، معماری و عکاسی در بوشهر | دوره‌های مهارتی')]
class extends Component {
    //
}; ?>


<div>
    <x-slot:meta_description>
        {{__('آموزشگاه تخصصی کامپیوتر، حسابداری، برنامه‌نویسی، معماری و عکاسی در بوشهر با دوره‌های مهارتی کاربردی. آموزش حضوری، مدرک معتبر و آمادگی ورود به بازار کار.')}}
    </x-slot:meta_description>
    <x-slot:og_title>
        {{__('آموزش مهارت‌های کامپیوتر، حسابداری و عکاسی در بوشهر')}}
    </x-slot:og_title>
    <x-slot:og_description>
        {{__('آموزش حضوری مهارت‌های کاربردی با مدرک معتبر در بوشهر')}}
    </x-slot:og_description>
    <x-slot:tw_title>
        {{__('آموزشگاه کامپیوتر و حسابداری در بوشهر')}}
    </x-slot:tw_title>
    <x-slot:tw_description>
        {{__('آموزش مهارت‌های کاربردی با مدرک معتبر در بوشهر')}}
    </x-slot:tw_description>

    {{-------------  Page Content ---------------}}
    <flux:heading size="xl" level="1">{{__('آموزشگاه کامپیوتر، حسابداری و عکاسی بوشهر')}}</flux:heading>
    <flux:text class="mt-2 mb-6 text-base text-justify leading-8">
        {{__('آموزشگاه آی‌تک در بوشهر برگزارکننده دوره‌های مهارتی کامپیوتر، برنامه‌نویسی، حسابداری، معماری و عکاسی است.
تمامی دوره‌ها به‌صورت حضوری، پروژه‌محور و متناسب با نیاز بازار کار برگزار می‌شوند.
هدف ما آموزش مهارت‌هایی است که واقعاً منجر به اشتغال و پیشرفت شغلی شوند.')}}
    </flux:text>
    <flux:separator variant="subtle" />
    <div class="container mx-auto py-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7 gap-6" >
        <a href="{{route('exams')}}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">{{ __('آزمونها') }}<flux:icon name="arrow-up-left" class="mr-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>

        <a href="{{route('icdl.purchase')}}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">{{ __('دوره ها') }}<flux:icon name="arrow-up-left" class="mr-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>

        <a href="{{route('written_questions')}}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
                <flux:heading class="flex items-center gap-2">{{ __('نمونه سوالات') }}<flux:icon name="arrow-up-left" class="mr-auto text-zinc-400" variant="micro" /></flux:heading>
            </flux:card>
        </a>

    </div>

</div>

