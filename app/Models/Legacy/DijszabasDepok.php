<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DijszabasDepok extends LegacyModel
{
    protected $table = 'dijszabas_depok';

    protected $primaryKey = 'dd_id';

    protected function casts(): array
    {
        return [
            'dd_datum_ido' => 'datetime',
            'dd_datum_tol' => 'date',
            'dd_datum_ig' => 'date',
        ];
    }

    public function depo(): BelongsTo
    {
        return $this->belongsTo(Depok::class, 'dd_depokod', 'dk_depokod');
    }
}
