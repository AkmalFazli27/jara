@props(['title' => 'Jara'])

<div {{ $attributes->merge(['class' => 'rounded-xl bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-200']) }} style="width: 32px; height: 32px;">
    <svg width="16" height="16" fill="none" stroke="white" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M9 11l3 3L22 4" />
        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
    </svg>
    <span class="sr-only">{{ $title }}</span>
</div>
