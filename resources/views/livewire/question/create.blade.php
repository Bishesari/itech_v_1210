<?php

use App\Models\Option;
use App\Models\Question;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Livewire\Volt\Component;

new class extends Component {
    public string $standard_id = '';
    public string $chapter_id = '';

    public string $text = '';
    public bool $is_final = false;
    public string $difficulty = 'easy';
    public array $options = ['', '', '', ''];
    public array $dir = [false, false, false, false];
    public int $correct = 0;

    public function mount($sid, $cid)
    {
        $this->standard_id = $sid;
        $this->chapter_id = $cid;
    }

    protected function rules(): array
    {
        return [
            'standard_id' => ['required', 'numeric'],
            'chapter_id' => ['required', 'numeric'],
            'text' => ['required', 'min:3'],
            'options.*' => ['required', 'min:3']
        ];
    }

    public function add_question(): void
    {
        $question = Question::create([
            'chapter_id' => $this->chapter_id,
            'text' => $this->text,
            'difficulty' => $this->difficulty,
            'is_final' => $this->is_final,
            'assigned_by' => Auth::user()->id,
        ]);

        foreach ($this->options as $index => $text) {
            if ($this->dir[$index]) {
                $dir = 'ltr';
            } else {
                $dir = 'rtl';
            }
            Option::create([
                'question_id' => $question->id,
                'text' => $text,
                'dir' => $dir,
                'is_correct' => $index == $this->correct,
            ]);
        }
        $url = URL::signedRoute('questions', ['sid' => $this->standard_id, 'cid' => $this->chapter_id]);
        redirect($url);
    }
}; ?>

<section class="w-full">

    <div class="relative w-full mb-2">
        <flux:heading size="xl" level="1">{{ __('سوالات فصل') }}</flux:heading>
        <flux:text color="blue" size="lg" class="my-2">{{ __('بخش درج سوال جدید') }}</flux:text>
        <flux:separator variant="subtle"/>
    </div>
    <form wire:submit="add_question" class="grid mt-5 sm:w-[400px]" autocomplete="off" autofocus>

        <!-- Standard select menu... -->
        <flux:select wire:model.live="standard_id" variant="listbox" placeholder="استانداردی انتخاب کنید ..."
                     label="استاندارد" searchable class="mb-5">
            @foreach (\App\Models\Standard::all() as $standard)
                <flux:select.option value="{{$standard->id}}">{{ $standard->name_fa }}</flux:select.option>
            @endforeach
        </flux:select>

        <!-- Chapter select menu... -->
        <flux:select wire:model.live="chapter_id" wire:key="{{ $standard_id }}" variant="listbox"
                     placeholder="سرفصل را انتخاب کنید ..."
                     label="فصل" class="mb-5">
            @foreach (\App\Models\Chapter::whereStandardId($standard_id)->get() as $chapter)
                <flux:select.option value="{{$chapter->id}}">{{ $chapter->title }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:textarea rows="4" wire:model="text" label="متن سوال" resize="none" class="mb-6"/>

        @foreach ($options as $i => $opt)
            <div class="flex justify-between mb-0.5">
                <flux:label>{{__('گزینه '). $i+1 }}</flux:label>
                <flux:checkbox wire:model="dir.{{ $i }}" label="متن لاتین (چپ به راست)"/>
            </div>
            <flux:textarea rows="2" wire:model="options.{{ $i }}" resize="none" class="mb-5"/>
        @endforeach

        <div class="grid grid-cols-2 gap-4 mb-5">
            <flux:select variant="listbox" wire:model="difficulty" placeholder="انتخاب کنید..." label="سختی سوال"
                         clearable>
                @foreach (\App\Models\Question::CLUSTERS as $key => $label)
                    <flux:select.option value="{{$key}}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select variant="listbox" wire:model="correct" placeholder="انتخاب کنید..." label="گزینه صحیح"
                         clearable>
                @foreach ($options as $i => $opt)
                    <flux:select.option value="{{$i}}">{{__('گزینه '). $i+1 }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="flex justify-between">
            <flux:button href="{{URL::signedRoute('questions', ['sid'=>$standard_id, 'cid'=>$chapter_id] )}}"
                         variant="primary" color="zinc" wire:navigate
                         size="sm" tabindex="-1">{{__('انصراف')}}</flux:button>
            <flux:field variant="inline">
                <flux:checkbox wire:model="is_final"/>
                <flux:label>{{__('پرتکرار نهایی')}}</flux:label>
            </flux:field>
            <flux:button type="submit" variant="primary" color="sky" size="sm"
                         class="cursor-pointer">{{__('ذخیره')}}</flux:button>
        </div>

    </form>
</section>
