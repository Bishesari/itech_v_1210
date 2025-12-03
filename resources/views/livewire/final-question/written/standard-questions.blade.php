<?php

use App\Models\Question;
use App\Models\Standard;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.public')]
class extends Component {

    public Standard $standard;
    public $questions;

    public function mount(Standard $standard)
    {

        $this->standard = $standard;

        // گرفتن تمام سؤالات نهایی کتبی
        $this->questions = Question::whereIn(
            'chapter_id',
            $standard->chapters->pluck('id')
        )
            ->where('is_final', true)
            ->with('options') // بارگذاری گزینه‌ها
            ->orderBy('text')
            ->get();
    }

    public function rendering(View $view): void
    {
        $title = 'نمونه سوالات آزمون کتبی فنی و حرفه‌ای ' . $this->standard->name_fa;
        $view->title($title);
    }

}; ?>

<div class="container mx-auto">
    <flux:heading size="lg" class="text-center relative">
        {{ __('نمونه سوالات آزمون کتبی فنی و حرفه ای') }}
        <a href="{{ route('written_questions') }}" class="absolute left-0">
            <flux:icon name="arrow-uturn-left" class="text-blue-500" />
        </a>
    </flux:heading>
    <flux:heading size="lg" class="text-center">{{$standard->name_fa}}</flux:heading>
    <flux:subheading size="md" class="text-center">{{'کد: ' . $standard->code }}</flux:subheading>
    <flux:separator variant="subtle" class="mt-2 mb-3"/>

    <flux:accordion transition exclusive>
        @php($i=1)
        @foreach($questions as $q)
            <flux:accordion.item>
                <flux:accordion.heading>
                    <span>{{ $i++ }} - {{ $q->text }}</span>
                    <span class="text-gray-500">{{'(' . $q->id.'#)'}}</span>
                </flux:accordion.heading>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-1">
                @foreach($q->options as $o)
                    @if($o->is_correct)
                        @php($var = 'success')
                        @php($icon = 'check-circle')
                    @else
                        @php($var = 'secondary')
                        @php($icon = '')
                    @endif
                    <flux:accordion.content>
                        <flux:callout variant="{{$var}}" heading="{!! $o->text !!}" dir="{{$o->dir}}"
                                      icon='{{$icon}}'/>
                    </flux:accordion.content>
                @endforeach
                </div>
            </flux:accordion.item>
        @endforeach
    </flux:accordion>
</div>
