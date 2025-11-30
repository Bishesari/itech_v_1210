<?php

use App\Models\Exam;
use App\Models\ExamUser;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public Collection $exams;

    public function mount()
    {
        $this->exams = Exam::where('end_date', '>', now())->get();
    }

}; ?>

<section class="w-full">
    <div class="mb-4">
        <flux:heading size="xl" level="1">{{ __('آزمون‌ها') }}</flux:heading>
        <flux:text color="blue" size="lg" class="my-2">{{ __('لیست آزمون‌های کتبی فعال') }}</flux:text>
        <flux:separator variant="subtle"/>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($exams as $exam)
            @php
                $examUser = \App\Models\ExamUser::where('exam_id', $exam->id)
                    ->where('user_id', auth()->id())
                    ->first();
            @endphp

            <a href="{{ route('exam_start', ['exam'=>$exam]) }}">
                <flux:card class="p-4 hover:shadow-lg transition-all duration-300 hover:-translate-y-1
                                 hover:bg-zinc-50 dark:hover:bg-zinc-800">

                    {{-- عنوان و شناسه --}}
                    <div class="mb-3">
                        <flux:heading size="lg">{{ $exam->standard->name_fa }}</flux:heading>
                        <flux:text class="text-sm opacity-70 mt-1">
                            {{ __('شناسه آزمون: ') }} {{ $exam->id }}
                        </flux:text>
                    </div>

                    {{-- عنوان آزمون --}}
                    <flux:text class="font-medium text-lg my-2">
                        {{ $exam->title }}
                    </flux:text>

                    <flux:separator variant="subtle" class="my-2"/>

                    {{-- اطلاعات آزمون --}}
                    <div class="space-y-2 text-sm">
                        <flux:text class="flex justify-between">
                            <span>{{ __('تعداد سوالات') }}</span>
                            <span dir="ltr">{{ $exam->question_count }}</span>
                        </flux:text>

                        <flux:text class="flex justify-between">
                            <span>{{ __('زمان آزمون') }}</span>
                            <span dir="ltr">{{ $exam->exam_time }} {{ __('دقیقه') }}</span>
                        </flux:text>

                        <flux:text class="flex justify-between">
                            <span>{{ __('فعال از') }}</span>
                            <span dir="ltr">{{ $exam->jalali_start_date }}</span>
                        </flux:text>

                        <flux:text class="flex justify-between">
                            <span>{{ __('تا') }}</span>
                            <span dir="ltr">{{ $exam->jalali_end_date }}</span>
                        </flux:text>
                    </div>

                    <flux:separator variant="subtle" class="my-3"/>

                    {{-- وضعیت شرکت کاربر --}}
                    @if($examUser)
                        <div class="mt-3 p-2 rounded-xl
                                    bg-green-100 text-green-800
                                    dark:bg-green-900 dark:text-green-200 flex justify-between items-center">
                            <div>
                                <flux:text class="font-bold">
                                    {{ __('قبلاً شرکت کرده‌اید') }}
                                </flux:text>

                                <flux:text class="text-sm mt-1 opacity-80">
                                    {{ __('نمره شما:') }} {{ $examUser->score }}
                                </flux:text>
                            </div>
                            <flux:icon name="check-circle" class="w-6 h-6"/>
                        </div>
                    @else
                        <div class="mt-3 p-2 rounded-xl bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 flex items-center gap-2">
                            <flux:text>{{ __('شرکت‌نکرده') }}</flux:text>
                        </div>
                    @endif

                </flux:card>
            </a>
        @endforeach
    </div>
</section>
