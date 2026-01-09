<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DijszabasBelfoldNaplo extends LegacyModel
{
    protected $table = 'dijszabas_belfold_naplo';

    protected $primaryKey = 'db_id';

    protected function casts(): array
    {
        return [
            'db_datum_ido' => 'datetime',
            'db_datum_tol' => 'date',
            'db_datum_ig' => 'date',
        ];
    }

    public function ugyfel(): BelongsTo
    {
        return $this->belongsTo(Ugyfel::class, 'db_ugyfelkod', 'u_ugyfelkod');
    }
}
