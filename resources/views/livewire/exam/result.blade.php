<?php

use App\Models\ExamAnswer;
use App\Models\ExamUser;
use Livewire\Volt\Component;

new class extends Component {
    public ExamUser $examUser;
    public array $answers = [];

    public function mount(): void
    {
        // گرفتن پاسخ‌ها همراه با سوال و گزینه صحیح
        $this->answers = ExamAnswer::where('exam_user_id', $this->examUser->id)
            ->with(['question.options', 'option'])
            ->get()
            ->map(function ($answer) {
                $correctOption = $answer->question->options->firstWhere('is_correct', true);
                return [
                    'question' => $answer->question->text,
                    'user_answer' => $answer->option->text ?? '-',
                    'correct_answer' => $correctOption->text ?? '-',
                    'is_correct' => $answer->is_correct,
                ];
            })
            ->toArray();
    }

}; ?>


<section class="w-full">
    <div class="mb-2">
        <flux:heading size="xl" level="1">{{__('نتیجه آزمون')}}
            -> {{$examUser->exam->standard->name_fa}} </flux:heading>
        <flux:text color="{{ $examUser->score < 50 ? 'red' : 'blue' }}" size="xl"
                   class="my-2 font-bold">{{__('%')}} {{$examUser->score}} </flux:text>
        <flux:separator variant="subtle"/>
    </div>

    @foreach($answers as $i => $answer)
        @php( $var = 'danger')
        @if($answer['is_correct'])
            @php( $var = 'success')
        @endif

        <flux:callout variant="{{$var}}">
            <flux:callout.heading>{{$i + 1}} - {{$answer['question']}}</flux:callout.heading>
        </flux:callout>
    <div class="grid grid-cols-2 mt-1 mb-5 gap-x-2">
        <flux:callout heading="{!! $answer['user_answer'] !!}"/>
        <flux:callout heading="{!! $answer['correct_answer'] !!}"/>
    </div>
    @endforeach

</section>
