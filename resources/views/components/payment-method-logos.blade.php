@props(['align' => 'center', 'logos' => []])
@php
    $justify = $align === 'start' ? 'justify-start' : 'justify-center';

    $placeholders = [
        'Visa' => ['class' => 'text-5xl sm:text-8xl font-black italic tracking-tight', 'style' => 'color:#1A1F71'],
        'Mastercard' => null,
        'MTN' => ['class' => 'text-5xl sm:text-8xl font-black tracking-wide', 'style' => 'color:#FFCB05'],
        'Airtel' => ['class' => 'text-5xl sm:text-8xl font-black tracking-wide', 'style' => 'color:#ED1C24'],
    ];
@endphp
<div {{ $attributes->merge(['class' => "grid grid-cols-3 place-items-center gap-x-2 gap-y-2 sm:flex sm:flex-wrap sm:items-center sm:$justify sm:gap-x-6 sm:gap-y-4"]) }}>
    @forelse ($logos as $logo)
        @if (! empty($logo['image_path']))
            <img src="{{ \Illuminate\Support\Facades\Storage::url($logo['image_path']) }}" alt="{{ $logo['label'] }}" class="h-16 w-auto max-w-[12rem] object-contain sm:h-32 sm:max-w-[32rem]" title="{{ $logo['label'] }}">
        @elseif ($logo['label'] === 'Mastercard')
            <span class="flex items-center gap-2 sm:gap-4" title="Mastercard">
                <span class="relative flex h-16 w-24 shrink-0 items-center sm:h-28 sm:w-40">
                    <span class="absolute left-0 h-16 w-16 rounded-full sm:h-28 sm:w-28" style="background:#EB001B"></span>
                    <span class="absolute right-0 h-16 w-16 rounded-full opacity-80 sm:h-28 sm:w-28" style="background:#F79E1B"></span>
                </span>
                <span class="text-3xl font-bold text-white sm:text-5xl">Mastercard</span>
            </span>
        @elseif (isset($placeholders[$logo['label']]))
            <span class="{{ $placeholders[$logo['label']]['class'] }}" style="{{ $placeholders[$logo['label']]['style'] }}" title="{{ $logo['label'] }}">{{ $logo['label'] }}</span>
        @else
            <span class="text-3xl font-bold text-white sm:text-5xl" title="{{ $logo['label'] }}">{{ $logo['label'] }}</span>
        @endif
    @empty
        {{-- No logos configured yet — nothing to render. --}}
    @endforelse
</div>
