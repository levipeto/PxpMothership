<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UgyfelFelhasznaloApi extends LegacyModel
{
    protected $table = 'ugyfel_felhasznalo_api';

    protected $primaryKey = 'ufa_id';

    protected $hidden = [
        'ufa_cserekulcs',
    ];

    public function ugyfel(): BelongsTo
    {
        return $this->belongsTo(Ugyfel::class, 'ufa_ugyfelkod', 'u_ugyfelkod');
    }
}
