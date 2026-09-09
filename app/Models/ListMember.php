<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Keanggotaan kolaborasi daftar. Unique (list_id, user_id).
 *
 * @see ERD.md — list_members.
 */
class ListMember extends Model
{
    use HasFactory;

    protected $fillable = ['list_id', 'user_id', 'role'];

    public function list(): BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
