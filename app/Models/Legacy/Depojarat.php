<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depojarat extends LegacyModel
{
    protected $table = 'depojarat';

    protected $primaryKey = 'dj_id';

    public function depo(): BelongsTo
    {
        return $this->belongsTo(Depok::class, 'dj_depo', 'dk_depokod');
    }
}
