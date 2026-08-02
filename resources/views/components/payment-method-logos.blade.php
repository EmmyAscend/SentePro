@props(['align' => 'center', 'logos' => []])
@php
    $justify = $align === 'start' ? 'justify-start' : 'justify-center';

    $placeholders = [
        'Visa' => ['class' => 'text-2xl font-black italic tracking-tight', 'style' => 'color:#1A1F71'],
        'Mastercard' => null,
        'MTN' => ['class' => 'text-2xl font-black tracking-wide', 'style' => 'color:#FFCB05'],
        'Airtel' => ['class' => 'text-2xl font-black tracking-wide', 'style' => 'color:#ED1C24'],
    ];
@endphp
<div {{ $attributes->merge(['class' => "flex flex-wrap items-center $justify gap-x-10 gap-y-4"]) }}>
    @forelse ($logos as $logo)
        @if (! empty($logo['image_path']))
            <img src="{{ \Illuminate\Support\Facades\Storage::url($logo['image_path']) }}" alt="{{ $logo['label'] }}" class="h-8 w-auto max-w-[8rem] object-contain" title="{{ $logo['label'] }}">
        @elseif ($logo['label'] === 'Mastercard')
            <span class="flex items-center gap-2" title="Mastercard">
                <span class="relative flex h-7 w-10 shrink-0 items-center">
                    <span class="absolute left-0 h-7 w-7 rounded-full" style="background:#EB001B"></span>
                    <span class="absolute right-0 h-7 w-7 rounded-full opacity-80" style="background:#F79E1B"></span>
                </span>
                <span class="text-base font-bold text-white">Mastercard</span>
            </span>
        @elseif (isset($placeholders[$logo['label']]))
            <span class="{{ $placeholders[$logo['label']]['class'] }}" style="{{ $placeholders[$logo['label']]['style'] }}" title="{{ $logo['label'] }}">{{ $logo['label'] }}</span>
        @else
            <span class="text-base font-bold text-white" title="{{ $logo['label'] }}">{{ $logo['label'] }}</span>
        @endif
    @empty
        {{-- No logos configured yet — nothing to render. --}}
    @endforelse
</div>
