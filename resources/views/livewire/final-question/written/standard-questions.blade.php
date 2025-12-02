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
        @foreach($questions as $q)
            <div class="border rounded-lg p-4 mb-4 shadow-sm hover:shadow-md transition">
                <p class="font-semibold mb-2">{{ $q->text }}</p>

                <ul class="list-disc pl-5">
                    @foreach($q->options as $o)
                        <li class="@if($o->is_correct) text-green-600 font-bold @endif">
                            {{ $o->text }}
                        </li>
                    @endforeach
                </ul>

                @if($q->explanation)
                    <p class="text-gray-500 mt-2">توضیح: {{ $q->explanation }}</p>
                @endif
            </div>
        @endforeach
    @endif

</div>

