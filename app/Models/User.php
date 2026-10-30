<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public const ROLES = ['admin' => 'Beheerder', 'editor' => 'Redacteur'];

    /**
     * Only users with a role (not mass assignable) may use the admin panel:
     * admin (everything) or editor (content and documentation only). Grant
     * it with `php artisan info:admin` (--role=editor for editors).
     * is_admin is kept as a mirror of the admin role.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && ($this->isAdmin() || $this->isEditor());
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || ($this->role === null && $this->is_admin);
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    /**
     * Sets the role (null = no panel access) and keeps is_admin in sync.
     */
    public function assignRole(?string $role): void
    {
        $this->forceFill(['role' => $role, 'is_admin' => $role === 'admin'])->save();
    }
}
