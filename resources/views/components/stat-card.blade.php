@props(['label' => '', 'value' => 0, 'sub' => '', 'accent' => 'bg-indigo-50 text-indigo-700'])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200 p-5 shadow-sm']) }}>
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">{{ $label }}</p>
    <p class="text-3xl font-bold text-slate-900 mb-1">{{ $value }}</p>
    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $accent }}">{{ $sub }}</span>
</div>
