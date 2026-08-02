@props(['align' => 'center', 'logos' => []])
@php
    $justify = $align === 'start' ? 'justify-start' : 'justify-center';

    $placeholders = [
        'Visa' => ['class' => 'text-2xl sm:text-5xl font-black italic tracking-tight', 'style' => 'color:#1A1F71'],
        'Mastercard' => null,
        'MTN' => ['class' => 'text-2xl sm:text-5xl font-black tracking-wide', 'style' => 'color:#FFCB05'],
        'Airtel' => ['class' => 'text-2xl sm:text-5xl font-black tracking-wide', 'style' => 'color:#ED1C24'],
    ];
@endphp
<div {{ $attributes->merge(['class' => "grid grid-cols-3 place-items-center gap-x-4 gap-y-4 sm:flex sm:flex-wrap sm:items-center sm:$justify sm:gap-x-10 sm:gap-y-6"]) }}>
    @forelse ($logos as $logo)
        @if (! empty($logo['image_path']))
            <img src="{{ \Illuminate\Support\Facades\Storage::url($logo['image_path']) }}" alt="{{ $logo['label'] }}" class="h-8 w-auto max-w-[6rem] object-contain sm:h-16 sm:max-w-[16rem]" title="{{ $logo['label'] }}">
        @elseif ($logo['label'] === 'Mastercard')
            <span class="flex items-center gap-1.5 sm:gap-3" title="Mastercard">
                <span class="relative flex h-8 w-12 shrink-0 items-center sm:h-14 sm:w-20">
                    <span class="absolute left-0 h-8 w-8 rounded-full sm:h-14 sm:w-14" style="background:#EB001B"></span>
                    <span class="absolute right-0 h-8 w-8 rounded-full opacity-80 sm:h-14 sm:w-14" style="background:#F79E1B"></span>
                </span>
                <span class="text-sm font-bold text-white sm:text-2xl">Mastercard</span>
            </span>
        @elseif (isset($placeholders[$logo['label']]))
            <span class="{{ $placeholders[$logo['label']]['class'] }}" style="{{ $placeholders[$logo['label']]['style'] }}" title="{{ $logo['label'] }}">{{ $logo['label'] }}</span>
        @else
            <span class="text-sm font-bold text-white sm:text-2xl" title="{{ $logo['label'] }}">{{ $logo['label'] }}</span>
        @endif
    @empty
        {{-- No logos configured yet — nothing to render. --}}
    @endforelse
</div>
