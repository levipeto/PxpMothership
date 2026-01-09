<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DijszabasBelfold extends LegacyModel
{
    protected $table = 'dijszabas_belfold';

    protected $primaryKey = 'db_id';

    public function getDeletedColumn(): ?string
    {
        return 'db_torolve';
    }

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
