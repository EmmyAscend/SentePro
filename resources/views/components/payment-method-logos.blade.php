@props(['align' => 'center', 'desktopAlign' => null, 'logos' => []])
@php
    $justify = $align === 'start' ? 'justify-start' : 'justify-center';
    $desktopJustify = ($desktopAlign ?? $align) === 'start' ? 'lg:justify-start' : 'lg:justify-center';

    $placeholders = [
        'Visa' => ['class' => 'text-5xl sm:text-8xl lg:text-6xl font-extrabold italic tracking-tight', 'style' => 'color:#1A1F71'],
        'Mastercard' => null,
        'MTN' => ['class' => 'text-5xl sm:text-8xl lg:text-6xl font-extrabold tracking-wide', 'style' => 'color:#FFCB05'],
        'Airtel' => ['class' => 'text-5xl sm:text-8xl lg:text-6xl font-extrabold tracking-wide', 'style' => 'color:#ED1C24'],
    ];
@endphp
<div {{ $attributes->merge(['class' => "grid grid-cols-[repeat(3,max-content)] items-center $justify gap-x-[10px] gap-y-[10px] sm:flex sm:flex-wrap sm:items-center sm:$justify sm:gap-x-2 sm:gap-y-2 $desktopJustify"]) }}>
    @forelse ($logos as $logo)
        @if (! empty($logo['image_path']))
            <img src="{{ \Illuminate\Support\Facades\Storage::url($logo['image_path']) }}" alt="{{ $logo['label'] }}" class="h-16 w-auto max-w-[12rem] object-contain sm:h-32 sm:max-w-[32rem] lg:h-20 lg:max-w-[20rem]" title="{{ $logo['label'] }}">
        @elseif ($logo['label'] === 'Mastercard')
            <span class="flex items-center gap-2 sm:gap-4" title="Mastercard">
                <span class="relative flex h-16 w-24 shrink-0 items-center sm:h-28 sm:w-40 lg:h-20 lg:w-28">
                    <span class="absolute left-0 h-16 w-16 rounded-full sm:h-28 sm:w-28 lg:h-20 lg:w-20" style="background:#EB001B"></span>
                    <span class="absolute right-0 h-16 w-16 rounded-full opacity-80 sm:h-28 sm:w-28 lg:h-20 lg:w-20" style="background:#F79E1B"></span>
                </span>
                <span class="text-3xl font-bold text-white sm:text-5xl lg:text-4xl">Mastercard</span>
            </span>
        @elseif (isset($placeholders[$logo['label']]))
            <span class="{{ $placeholders[$logo['label']]['class'] }}" style="{{ $placeholders[$logo['label']]['style'] }}" title="{{ $logo['label'] }}">{{ $logo['label'] }}</span>
        @else
            <span class="text-3xl font-bold text-white sm:text-5xl lg:text-4xl" title="{{ $logo['label'] }}">{{ $logo['label'] }}</span>
        @endif
    @empty
        {{-- No logos configured yet — nothing to render. --}}
    @endforelse
</div>
