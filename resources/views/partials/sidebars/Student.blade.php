<flux:navlist.group :heading="__('سکوی توسعه')" class="grid">
    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('داشبرد دانشجو آموزشگاه') }}</flux:navlist.item>
</flux:navlist.group>

<flux:navlist.group :heading="__('آزمونهای کتبی')" class="grid" expandable :expanded="request()->routeIs(['exams'])" >
    <flux:navlist.item icon="user-group" href="{{route('exams')}}" :current="request()->routeIs('exams')" wire:navigate>{{ __('آزمونها') }}</flux:navlist.item>
</flux:navlist.group>
