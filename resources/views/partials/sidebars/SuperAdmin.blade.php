<flux:navlist.group :heading="__('سکوی توسعه')" class="grid">
    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('داشبرد سوپر ادمین') }}</flux:navlist.item>
</flux:navlist.group>

<flux:navlist.group :heading="__('اطلاعات پایه')" class="grid" expandable :expanded="request()->routeIs(['roles', 'institutes.for.sa', 'users.for.sa', 'fields', 'standards'])" >
    <flux:navlist.item icon="user-group" :href="route('roles')" :current="request()->routeIs('roles')" wire:navigate>{{ __('نقشهای کاربری') }}</flux:navlist.item>
    <flux:navlist.item icon="user-group" :href="route('institutes.for.sa')" :current="request()->routeIs('institutes.for.sa')" wire:navigate>
        {{ __('آموزشگاهها') }}
    </flux:navlist.item>
    <flux:navlist.item icon="user-group" :href="route('users.for.sa')" :current="request()->routeIs('users.for.sa')" wire:navigate>{{ __('کاربران آموزشگاهها') }}</flux:navlist.item>

    <flux:navlist.item icon="user-group" :href="route('fields')" :current="request()->routeIs('fields')" wire:navigate>{{ __('رشته های آموزشی') }}</flux:navlist.item>
    <flux:navlist.item icon="user-group" :href="route('standards')" :current="request()->routeIs('standards')" wire:navigate>{{ __('استانداردهای آموزشی') }}</flux:navlist.item>

</flux:navlist.group>


<flux:navlist.group :heading="__('بانک سوال')" class="grid" expandable :expanded="request()->routeIs(['questions', 'create_question'])" >
    <flux:navlist.item icon="user-group" href="{{URL::signedRoute('questions', ['sid'=>0, 'cid'=>0] )}}" :current="request()->routeIs('questions')" wire:navigate>{{ __('کل سوالات') }}</flux:navlist.item>
    <flux:navlist.item icon="user-group" href="{{URL::signedRoute('create_question', ['sid'=>0, 'cid'=>0] )}}" :current="request()->routeIs('create_question')" wire:navigate>{{ __('درج سوال') }}</flux:navlist.item>
</flux:navlist.group>

<flux:navlist.group :heading="__('آزمونهای کتبی')" class="grid" expandable :expanded="request()->routeIs(['exams', 'exam_create'])" >
    <flux:navlist.item icon="user-group" href="{{route('exams')}}" :current="request()->routeIs('exams')" wire:navigate>{{ __('آزمونها') }}</flux:navlist.item>
    <flux:navlist.item icon="user-group" href="{{route('exam_create')}}" :current="request()->routeIs('exam_create')" wire:navigate>{{ __('آزمون جدید') }}</flux:navlist.item>
</flux:navlist.group>
