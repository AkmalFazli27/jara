<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Check if the user has admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user has regular user role.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Scope query to search users by name or email.
     */
    public function scopeSearch($query, ?string $term)
    {
        return $query->when($term, function ($q, $term) {
            $q->where(function ($subQuery) use ($term) {
                $subQuery->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        });
    }

    /**
     * Scope query to filter users by role.
     */
    public function scopeRoleFilter($query, ?string $role)
    {
        return $query->when($role && in_array($role, ['admin', 'user'], true), function ($q) use ($role) {
            $q->where('role', $role);
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Daftar yang dimiliki user ini (lists.owner_id). */
    public function ownedLists(): HasMany
    {
        return $this->hasMany(TaskList::class, 'owner_id');
    }

    /** Baris keanggotaan kolaborasi user ini. */
    public function memberships(): HasMany
    {
        return $this->hasMany(ListMember::class);
    }
}
