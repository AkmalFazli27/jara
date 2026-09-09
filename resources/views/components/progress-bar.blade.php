@props(['progress' => 0])

@php($pct = max(0, min(100, (int) $progress)))

<div {{ $attributes->merge(['class' => 'h-2 bg-slate-100 rounded-full overflow-hidden']) }}>
    <div
        class="h-full rounded-full transition-all duration-500"
        style="width: {{ $pct }}%; background: linear-gradient(90deg, #4f46e5, #7c3aed);"
        role="progressbar"
        aria-valuenow="{{ $pct }}"
        aria-valuemin="0"
        aria-valuemax="100"
    ></div>
</div>
