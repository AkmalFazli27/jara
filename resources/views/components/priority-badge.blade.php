@props(['priority' => 'medium'])

@php
    $map = [
        'high' => ['label' => 'High', 'pill' => 'text-rose-600 bg-rose-50', 'dot' => 'bg-rose-500'],
        'medium' => ['label' => 'Medium', 'pill' => 'text-amber-600 bg-amber-50', 'dot' => 'bg-amber-500'],
        'low' => ['label' => 'Low', 'pill' => 'text-slate-500 bg-slate-100', 'dot' => 'bg-slate-400'],
    ];
    $meta = $map[$priority] ?? $map['medium'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full {$meta['pill']}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $meta['dot'] }}"></span>
    {{ $meta['label'] }}
</span>
