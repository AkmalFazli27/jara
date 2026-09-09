@props(['size' => 28, 'name' => null, 'initials' => null, 'color' => null])

@php
    $displayName = $name ?? ($attributes->get('alt') ?? 'U');
    $displayInitials = $initials ?? collect(explode(' ', trim($displayName)))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');
    $palette = ['#4f46e5', '#7c3aed', '#0ea5e9', '#10b981', '#f59e0b', '#f43f5e'];
    $bg = $color ?? $palette[abs(crc32($displayName)) % count($palette)];
    $px = (int) $size;
@endphp

<div
    {{ $attributes->merge(['class' => 'rounded-full flex items-center justify-center text-white font-semibold shrink-0 select-none']) }}
    style="width: {{ $px }}px; height: {{ $px }}px; background: {{ $bg }}; font-size: {{ (int) round($px * 0.36) }}px;"
    title="{{ $displayName }}"
    role="img"
    aria-label="{{ $displayName }}"
>{{ $displayInitials }}</div>
