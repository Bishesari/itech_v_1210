<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use App\Models\Standard;
use App\Models\Question;

new
#[Layout('components.layouts.public')]
#[Title('نمونه سوالات آزمون کتبی')]
class extends Component {
    public $standards;

    public function mount()
    {
        // گرفتن همه استانداردها و تعداد سؤالات پرتکرار
        $this->standards = Standard::orderBy('name_fa') // مرتب‌سازی حروف الفبا
        ->get()->map(function($standard) {
            $finalQuestionsCount = Question::whereIn(
                'chapter_id',
                $standard->chapters->pluck('id')
            )
                ->where('is_final', true)
                ->count();

            $standard->final_questions_count = $finalQuestionsCount;
            return $standard;
        })
            // فقط استانداردهایی که حداقل یک سوال نهایی دارند
            ->filter(function($standard) {
                return $standard->final_questions_count > 0;
            });
    }
};
?>

<div class="container mx-auto py-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-4 gap-6">
    @foreach($standards as $standard)
        <a href="{{route('written_standard_questions', $standard)}}" aria-label="Latest on our blog">
            <flux:card size="sm" class="hover:bg-zinc-100 dark:hover:bg-zinc-600">
                <flux:heading class="flex items-center gap-2">{{ $standard->name_fa }}<flux:icon name="arrow-up-left" class="mr-auto text-zinc-400" variant="micro" /></flux:heading>
                <flux:text class="mt-2">کد استاندارد: {{ $standard->code }}</flux:text>
                <flux:text class="mt-2">تعداد سؤالات نهایی: {{ $standard->final_questions_count }}</flux:text>
            </flux:card>
        </a>
    @endforeach
</div>



