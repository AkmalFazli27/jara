<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Daftar/project. Tabel `lists` (model bernama TaskList karena
 * `List` adalah reserved word di PHP).
 *
 * @see ERD.md — lists.owner_id, cascade on delete.
 */
class TaskList extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $fillable = ['owner_id', 'name', 'description', 'is_archived'];

    protected function casts(): array
    {
        return ['is_archived' => 'boolean'];
    }

    /** Pemilik daftar. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Anggota kolaborasi (termasuk owner, sesuai ERD aturan 3). */
    public function members(): HasMany
    {
        return $this->hasMany(ListMember::class, 'list_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'list_id');
    }

    /**
     * Warna UI-only untuk sidebar (tidak ada kolom di DB).
     * Deterministik dari id agar stabil antar request.
     */
    protected function color(): Attribute
    {
        return Attribute::get(function (): string {
            $palette = ['#4f46e5', '#7c3aed', '#0ea5e9', '#10b981', '#f59e0b', '#f43f5e'];

            return $palette[abs(crc32((string) $this->id)) % count($palette)];
        });
    }

    /** Progress 0–100 dari tasks.is_completed (kontrak F-18). */
    protected function progress(): Attribute
    {
        return Attribute::get(function (): int {
            $total = $this->tasks()->count();

            if ($total === 0) {
                return 0;
            }

            return (int) round($this->tasks()->where('is_completed', true)->count() / $total * 100);
        });
    }
}
