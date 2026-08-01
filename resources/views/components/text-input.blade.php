@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-slate-950 border-white/10 text-white placeholder:text-slate-500 focus:border-lime-400 focus:ring-lime-400 rounded-md shadow-sm disabled:opacity-50']) }}>
