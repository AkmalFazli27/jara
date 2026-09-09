@props(['message' => 'No tasks match your filters.'])

<div {{ $attributes->merge(['class' => 'px-5 py-12 text-center text-sm text-slate-400']) }}>
    {{ $message }}
</div>
