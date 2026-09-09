{{-- Visual status. DB hanya punya is_completed, jadi status kanban
      "in-progress"/"review" dipetakan sebagai varian aktif (UI-only). --}}
@props(['status' => null, 'completed' => false])

@php
    $map = [
        'todo' => ['label' => 'To Do', 'class' => 'text-slate-600 bg-slate-100'],
        'in-progress' => ['label' => 'In Progress', 'class' => 'text-indigo-700 bg-indigo-50'],
        'review' => ['label' => 'Review', 'class' => 'text-violet-700 bg-violet-50'],
        'done' => ['label' => 'Done', 'class' => 'text-emerald-700 bg-emerald-50'],
    ];

    if ($status === null) {
        $status = $completed ? 'done' : 'todo';
    }

    $meta = $map[$status] ?? $map['todo'];
@endphp

<span {{ $attributes->merge(['class' => "text-xs font-medium px-2.5 py-0.5 rounded-full {$meta['class']}"]) }}>{{ $meta['label'] }}</span>
