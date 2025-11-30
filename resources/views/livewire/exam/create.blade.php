<?php

use App\Models\Exam;
use App\Models\Question;
use App\Models\Standard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    public string $standard_id = '';
    public Collection $standards;
    public Collection $chapters;
    public array $selected_chapters = [];
    public int $total_questions = 0;

    public string $title = '';
    public string $question_count = '';
    public int $exam_time = 0;
    public string $st_date;
    public string $st_time;
    public string $en_date;
    public string $en_time;


    public function mount(): void
    {
        $this->standards = Standard::all();
        $this->chapters = new Collection();
    }

    public function updatedStandardId(): void
    {
        $this->chapters = Standard::find($this->standard_id)?->chapters ?? new Collection();
        $this->selected_chapters = [];
        $this->total_questions = 0;
    }

    public function updatedSelectedChapters(): void
    {
        $this->total_questions = $this->chapters
            ->whereIn('id', $this->selected_chapters)
            ->sum(fn($chapter) => $chapter->questions->count());
    }


    public function add_exam()
    {
        $exam = Exam::create([
                'standard_id' => $this->standard_id,
                'title' => $this->title,
                'question_count' => $this->question_count,
                'exam_time' => $this->exam_time,
                'start_date' => Carbon::parse("{$this->st_date} {$this->st_time}"),
                'end_date' => Carbon::parse("{$this->en_date} {$this->en_time}"),
            ]
        );

        $questionIds = Question::whereIn('chapter_id', $this->selected_chapters)
            ->pluck('id')
            ->shuffle()
            ->take($this->question_count)
            ->toArray();
        $exam->questions()->sync($questionIds);
        $this->redirectRoute('exams');
    }


}; ?>

<section class="w-full">
    <div class="mb-2">
        <flux:heading size="xl" level="1">{{ __('آزمونها') }}</flux:heading>
        <flux:text color="blue" size="lg" class="my-2">{{ __('ایجاد آزمون جدید') }}</flux:text>
        <flux:separator variant="subtle"/>
    </div>

    <form wire:submit="add_exam" class="grid gap-5 mt-5 sm:w-[400px]" autocomplete="off" autofocus>

        <!-- Standard select menu... -->
        <flux:select wire:model.live="standard_id" variant="listbox" placeholder="استانداردی انتخاب کنید ..."
                     label="استاندارد" searchable class="mb-5">
            @foreach ($standards as $standard)
                <flux:select.option value="{{$standard->id}}">{{ $standard->name_fa }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:checkbox.group wire:model.live="selected_chapters" variant="cards" class="flex-col"
                             wire:key="{{ $standard_id }}">
            <div wire:loading wire:target="standard_id" class="text-amber-500 dark:text-amber-300">
                <flux:icon.loading/>
            </div>
            @if($standard_id and $chapters->isEmpty())
                <flux:text>{{__('فصلی برای استاندارد تعریف نشده...')}}</flux:text>
            @endif
            @foreach ($chapters as $chapter)
                @php
                    $questionCount = $chapter->questions->count();
                @endphp
                @if($questionCount > 0)
                    <flux:checkbox
                        value="{{ $chapter->id }}"
                        label="{{$chapter->number.' - '.$chapter->title }}"
                        description="{{ __('تعداد سوالات: ') }}{{ $questionCount }}" class="cursor-pointer"
                    />
                @else
                    <flux:checkbox
                        label="{{ $chapter->number.' - '.$chapter->title }}"
                        description="(بدون سؤال)" disabled
                    />

                @endif
            @endforeach
        </flux:checkbox.group>

        @if($standard_id and $chapters->isNotEmpty())
            <flux:input wire:model="title" label="عنوان آزمون" type="text" class:input="text-center" required/>

            <div class="grid grid-cols-2 space-x-3">
                <flux:input wire:model="question_count" label="تعداد سوالها"
                            badge="{{__('انتخاب شده: ') . $total_questions}}" type="text" class:input="text-center" required/>
                <flux:input wire:model="exam_time" label="مدت آزمون(دقیقه)"
                            type="text" class:input="text-center" required/>
            </div>
            <flux:field>
                <flux:label>{{__('تاریخ و زمان شروع')}}</flux:label>
                <div class="grid grid-cols-2 space-x-3" dir="ltr">
                    <flux:date-picker locale="fa-IR" wire:model="st_date" with-today selectable-header/>
                    <flux:time-picker wire:model="st_time" type="input" :dropdown="false"/>
                </div>
            </flux:field>

            <flux:field>
                <flux:label>{{__('تاریخ و زمان پایان')}}</flux:label>
                <div class="grid grid-cols-2 space-x-3" dir="ltr">
                    <flux:date-picker locale="fa-IR" wire:model="en_date" with-today selectable-header/>
                    <flux:time-picker wire:model="en_time" type="input" :dropdown="false"/>
                </div>
            </flux:field>


            <div class="flex justify-between flex-row-reverse">
                <flux:button type="submit" variant="primary" color="sky" size="sm"
                             class="cursor-pointer">{{__('ذخیره')}}</flux:button>
                <flux:button href="{{route('exam_create')}}" variant="primary" color="zinc" wire:navigate
                             size="sm">{{__('انصراف')}}</flux:button>
            </div>
        @endif
    </form>
</section>
