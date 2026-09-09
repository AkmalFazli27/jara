<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tugas dalam daftar.
 *
 * Catatan pemetaan desain → DB: desain punya status 4 kolom
 * (todo/in-progress/review/done), assignee, tags, subtasks.
 * DB hanya punya is_completed/completed_at, priority, deadline
 * (lihat ERD.md). Kolom yang tidak ada TIDAK ditambah di sini.
 *
 * @see ERD.md — tasks.
 */
class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id', 'title', 'description', 'priority',
        'deadline', 'is_completed', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }

    /** Label tanggal ala desain ("Sep 14"), "—" bila tanpa deadline. */
    protected function dueLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->deadline ? $this->deadline->format('M j') : '—');
    }

    /** Overdue = punya deadline lewat + belum selesai (kontrak F-17). */
    protected function isOverdue(): Attribute
    {
        return Attribute::get(fn (): bool => ! $this->is_completed && $this->deadline !== null && $this->deadline->isPast());
    }
}
