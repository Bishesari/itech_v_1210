<?php

use App\Models\Question;
use App\Models\Standard;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.public')]
#[Title('نمونه سوالات آزمون کتبی')]
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

}; ?>
<div class="container mx-auto py-6">

    <h1 class="text-3xl font-bold mb-6">
        سؤالات نهایی کتبی استاندارد: {{ $standard->name_fa }} ({{ $standard->code }})
    </h1>

    <a href="{{ route('written_questions') }}" class="inline-block mb-6 text-blue-600 hover:underline">
        ← بازگشت به لیست استانداردها
    </a>

    @if($questions->isEmpty())
        <p class="text-gray-500">فعلاً سؤالی به عنوان پرتکرار نهایی ثبت نشده است.</p>
    @else
        <flux:accordion transition exclusive>
            @foreach($questions as $q)
                <flux:accordion.item>
                    <flux:accordion.heading class="font-semibold">{{$q->id . ' - ' . $q->text }}</flux:accordion.heading>
                    @foreach($q->options as $o)
                        <flux:accordion.content><span class="@if($o->is_correct) text-green-600 font-bold @endif">{{ $o->text }}</span></flux:accordion.content>
                    @endforeach
                </flux:accordion.item>
            @endforeach
        </flux:accordion>
    @endif

</div>

