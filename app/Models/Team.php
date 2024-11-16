<?php

namespace App\Models;

use App\Scopes\TeamVisibilityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
        ];
    }

    /** @return HasMany<\App\Models\Role, self> */
    public function roles(): HasMany
    {
        return $this->hasMany(\App\Models\Role::class);
    }

    protected static function booted()
    {
        parent::booted();

        // static::addGlobalScope(new TeamVisibilityScope);

        static::creating(function ($team) {
            $team->personal_team = false; // Forza sempre false
        });

        static::saving(function (Team $team) {
            if (!$team->user_id) {
                $team->user_id = auth()->id();
            }
        });

        // add user to team when created
        static::creating(function ($team) {

            if (!$team->user_id) {

                $team->user_id = auth()->id();
            }

            // Imposta personal_team = true solo durante la registrazione
            // $team->personal_team = request()->is('register');
        });
    }

}
