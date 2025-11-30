<?php

use App\Models\Exam;
use App\Models\ExamUser;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {

    public Exam $exam;

    public ?ExamUser $examUser;
    public function mount(): void
    {
        $this->examUser = ExamUser::where('exam_id', $this->exam->id)->where('user_id', Auth::id())->first();
    }
    public function examBegin()
    {
        // ترتیب تصادفی سوالات
        $questionIds = $this->exam->questions()->pluck('questions.id')->shuffle()->toArray();
        Auth::user()->exams()->attach($this->exam->id, [
            'started_at' => now(),
            'question_order' => json_encode($questionIds),
        ]);

        $examUser = ExamUser::where('exam_id', $this->exam->id)->where('user_id', Auth::id())->first();
        return redirect()->route('exam.take', ['examUser' => $examUser->id]);

    }

}; ?>

<section class="w-full">
    <div class="mb-2">
        <flux:heading size="xl" level="1">{{ __('آزمونها') }}</flux:heading>
        <flux:text color="blue" size="lg" class="my-2">{{ __('لیست آزمونهای کتبی') }}</flux:text>
        <flux:separator variant="subtle"/>
    </div>
    <div class="flex justify-center">
        @if($examUser)
            @if(!is_null($examUser->finished_at))
                <flux:callout icon="exclamation-triangle" variant="danger" inline class="w-[500px]">
                    <flux:callout.heading>{{__('امتیاز این آزمون برای شما قبلا ثبت شده است!')}}</flux:callout.heading>
                    <x-slot name="actions">
                        <flux:button size="sm" variant="primary" color="red" href="{{route('exam.result', ['examUser' => $examUser])}}">{{__('دیدن نتیجه')}}</flux:button>
                    </x-slot>
                </flux:callout>
            @else
                @if(now()->greaterThan($examUser->started_at->addMinutes($examUser->exam->exam_time)))
                    <flux:callout icon="exclamation-triangle" variant="warning" inline class="w-[500px]">
                        <flux:callout.heading>{{__('قبلا در این آزمون شرکت کرده اید!')}}</flux:callout.heading>
                        <flux:callout.text>{{__('مهلت آزمون برای شما به پایان رسیده است.')}}</flux:callout.text>
                        <x-slot name="actions">
                            <flux:button size="sm" variant="primary" color="fuchsia" href="{{route('exam.result', ['examUser' => $examUser])}}">{{__('دیدن نتیجه')}}</flux:button>
                        </x-slot>
                    </flux:callout>
                @else
                    <flux:callout icon="exclamation-triangle" variant="warning" inline class="w-[480px]">
                        <flux:callout.heading>{{__('آزمون در حال انجام است!')}}</flux:callout.heading>
                        <x-slot name="actions">
                            <flux:button variant="primary" color="fuchsia" size="sm" href="{{route('exam.take', ['examUser' => $examUser->id])}}">{{__('برو به آزمون')}}</flux:button>
                        </x-slot>
                    </flux:callout>
                @endif
            @endif
        @else
            <flux:button wire:click="examBegin" variant="primary" color="green" class="cursor-pointer">
                {{__('شروع آزمون')}}
            </flux:button>

        @endif
    </div>

</section>
