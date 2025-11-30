<?php

use App\Models\Chapter;
use App\Models\Question;
use App\Models\Standard;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {

    use WithPagination;

    public string $standard_id = '0';
    public string $chapter_id = '0';

    public function mount($sid, $cid): void
    {
        $this->standard_id = $sid;
        $this->chapter_id = $cid;
    }

    public function with(): array
    {
        $query = Question::query();

        if ($this->standard_id != 0 && $this->chapter_id == 0) {
            $query = Standard::find($this->standard_id)->questions();
        }
        elseif ($this->standard_id != 0 && $this->chapter_id != 0) {
            $query = Chapter::find($this->chapter_id)->questions();
        }

        return [
            'questions' => $query->latest()->paginate(5),
        ];
    }

    public function updatedStandardId(): void
    {
        $this->chapter_id = 0;
    }


}; ?>
<section class="w-full">
    <div class="relative w-full mb-2">
        <flux:heading size="xl" level="1">{{ __('بانک کلی سوالات') }}</flux:heading>
        <flux:text size="lg" class="my-2">{{ __('بخش فیلتر سوال') }}</flux:text>
        <flux:separator variant="subtle"/>
    </div>


    <div class="grid sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 mb-3">
        <!-- Standard select menu... -->
        <flux:select wire:model.live="standard_id" variant="listbox" placeholder="استانداردی انتخاب کنید ..."
                     searchable size="sm">
            <flux:select.option value="0">{{__('همه استانداردها')}}</flux:select.option>
            @foreach (\App\Models\Standard::all() as $standard)
                <flux:select.option value="{{$standard->id}}">{{ $standard->name_fa }}</flux:select.option>
            @endforeach
        </flux:select>

        <!-- Chapter select menu... -->
        <flux:select wire:model.live="chapter_id" wire:key="{{ $standard_id }}" variant="listbox"
                     placeholder="سرفصل را انتخاب کنید ..." size="sm">
            <flux:select.option value="0">{{__('همه فصلها')}}</flux:select.option>
            @foreach (\App\Models\Chapter::whereStandardId($standard_id)->get() as $chapter)
                <flux:select.option value="{{$chapter->id}}">{{ $chapter->title }}</flux:select.option>
            @endforeach
        </flux:select>
        <div class="flex justify-between">
            <flux:button variant="ghost" size="sm" disabled>{{$questions->total()}} {{__('رکورد')}}</flux:button>
            <div wire:loading class="text-amber-500 dark:text-amber-300"><flux:icon.loading /></div>
            <flux:button href="{{URL::signedRoute('create_question', ['sid'=>$standard_id, 'cid'=>$chapter_id] )}}"
                         variant="primary" color="sky" size="sm" class="cursor-pointer">{{__('جدید')}}</flux:button>
        </div>
    </div>

    @foreach($questions as $question)
        <flux:callout color="zinc" inline>
            <flux:callout.heading>#{{$question->id}} - {{$question->text}} <span class="text-xs text-green-500">@if($question->is_final) {{__('(پرتکرار نهایی)')}} @endif</span></flux:callout.heading>
            <flux:text size="sm">{{__('فصل')}} {{$question->chapter->number}}
                : {{$question->chapter->title}} {{'( ' . $question->chapter->standard->name_fa . ' )'}} {{'( '. $question->maker->profile->l_name_fa . ' )'}}</flux:text>

            <x-slot name="actions">
                <flux:button variant="subtle" href="{{URL::signedRoute('edit_question', ['question'=>$question] )}}" size="xs">
                    {{__('ویرایش')}}
                </flux:button>
            </x-slot>

        </flux:callout>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-1 mt-1 mb-6">
            @foreach($question->options as $option)
                @if($option->is_correct)
                    @php($var = 'success')
                    @php($icon = 'check-circle')
                @else
                    @php($var = 'secondary')
                    @php($icon = '')
                @endif
                <flux:callout variant="{{$var}}" heading="{!! $option->text !!}" dir="{{$option->dir}}"
                              icon='{{$icon}}'/>
            @endforeach
        </div>
    @endforeach
    <flux:pagination :paginator="$questions" />

</section>
