@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-lime-400 text-start text-base font-medium text-lime-300 bg-lime-400/10 focus:outline-none focus:text-lime-200 focus:bg-lime-400/20 focus:border-lime-400 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-400 hover:text-slate-200 hover:bg-white/5 hover:border-white/20 focus:outline-none focus:text-slate-200 focus:bg-white/5 focus:border-white/20 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
