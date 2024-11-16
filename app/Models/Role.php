<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends SpatieRole
{
    //

    /** @return BelongsTo<\App\Models\Team, self> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Team::class);
    }

}
