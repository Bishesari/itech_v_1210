<?php

use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Livewire\Volt\Component;

new class extends Component
{

    public Question $question;

    public string $standard_id = '';
    public string $chapter_id = '';

    public array $options = [];
    public array $dir = [false, false, false, false];

    public string $difficulty = '';
    public int $correct;
    public bool $is_final;

    public string $text = '';

    public function mount()
    {
        $this->text = $this->question->text;
        $this->standard_id = $this->question->chapter->standard->id;
        $this->chapter_id = $this->question->chapter->id;
        $this->difficulty = $this->question->difficulty;
        $this->is_final = $this->question->is_final;

        foreach ($this->question->options as $i => $option) {
            $this->options[$i] = $option['text'];
            $this->dir[$i] = ($option['dir'] === 'ltr');
            if ($option['is_correct'] == 1) {
                $this->correct = $i;
            }
        }
    }

    public function update_question(): void
    {
        $question = $this->question;
        $question->update([
            'chapter_id' => $this->chapter_id,
            'text' => $this->text,
            'difficulty' => $this->difficulty,
            'is_final' => $this->is_final,
            'assigned_by' => Auth::id(),
        ]);
        foreach ($question->options as $i => $opt) {

            if ($this->dir[$i]) {$dir = 'ltr';} else {$dir = 'rtl';}
            $is_correct = $i == $this->correct;
            $opt->update([
                'text' => $this->options[$i],
                'dir' => $dir,
                'is_correct' => $is_correct,
            ]);

        }
        $url = URL::signedRoute('questions', ['sid' => $this->standard_id, 'cid' => $this->chapter_id]);
        redirect($url);
}


}; ?>


<section class="w-full">
    <div class="relative w-full mb-2">
        <flux:heading size="xl" level="1">{{ __('سوالات فصل') }}</flux:heading>
        <flux:text color="blue" size="lg" class="my-2">{{ __('بخش ویرایش سوال') }}</flux:text>
        <flux:separator variant="subtle"/>
    </div>
    <form wire:submit="update_question" class="grid mt-5 sm:w-[400px]" autocomplete="off" autofocus>


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
            <flux:button href="{{URL::signedRoute('questions', ['sid'=>$question->chapter->standard->id, 'cid'=> $question->chapter->id] )}}"
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
