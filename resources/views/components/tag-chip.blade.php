@props(['label' => ''])

<span {{ $attributes->merge(['class' => 'text-xs text-slate-500 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-md font-medium']) }}>{{ $label ?: $slot }}</span>
