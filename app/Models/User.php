<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel\Concerns\HasTenancy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;

    public const ROLE_IMPORTANCE = [
        'super_admin' => 1,
        'Amministratore' => 2,
        'Coordinatore' => 3,
        'Avvocato' => 4,
        'Cliente' => 5,
        'Segreteria' => 6
    ];

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_banned'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

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



    protected function scopeOrderByRoleImportance($query, $direction = 'asc')
    {
        $cases = collect(self::ROLE_IMPORTANCE)
            ->map(fn($order, $role) => "WHEN '{$role}' THEN {$order}")
            ->join(' ');

        return $query
            ->leftJoin(DB::raw("(
            SELECT model_id, 
                   MIN(CASE roles.name {$cases} ELSE 99 END) as role_importance,
                   MIN(roles.name) as role_name
            FROM model_has_roles
            LEFT JOIN roles ON model_has_roles.role_id = roles.id
            GROUP BY model_id
        ) as user_roles"), 'users.id', '=', 'user_roles.model_id')
            ->orderBy('user_roles.role_importance', $direction)
            ->select('users.*');
    }


    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Amministratore');
    }

    public function isCoordinator(): bool
    {
        return $this->hasRole('Coordinatore');
    }

    public function isLawyer(): bool
    {
        return $this->hasRole('Avvocato');
    }

    public function isClient(): bool
    {
        return $this->hasRole('Cliente');
    }

    public function isSecretary(): bool
    {
        return $this->hasRole('Segreteria');
    }

    // detach user from owned teams if user is updated

}
