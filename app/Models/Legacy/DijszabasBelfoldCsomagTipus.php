<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DijszabasBelfoldCsomagTipus extends LegacyModel
{
    protected $table = 'dijszabas_belfold_csomag_tipus';

    protected $primaryKey = 'dbcst_id';

    public function getDeletedColumn(): ?string
    {
        return 'dbcst_torolve';
    }

    protected function casts(): array
    {
        return [
            'dbcst_datum_ido' => 'datetime',
            'dbcst_datum_tol' => 'date',
            'dbcst_datum_ig' => 'date',
        ];
    }

    public function ugyfel(): BelongsTo
    {
        return $this->belongsTo(Ugyfel::class, 'dbcst_ugyfelkod', 'u_ugyfelkod');
    }
}
