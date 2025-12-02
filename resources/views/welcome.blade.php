<x-layouts.public title="آموزشگاه آی تک - خوش آمدید.">

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

</x-layouts.public>
