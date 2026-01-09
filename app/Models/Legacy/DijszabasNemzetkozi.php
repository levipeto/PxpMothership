<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DijszabasNemzetkozi extends LegacyModel
{
    protected $table = 'dijszabas_nemzetkozi';

    protected $primaryKey = 'dn_id';

    public function getDeletedColumn(): ?string
    {
        return 'dn_torolve';
    }

    protected function casts(): array
    {
        return [
            'dn_datum_ido' => 'datetime',
            'dn_datum_tol' => 'date',
            'dn_datum_ig' => 'date',
        ];
    }

    public function ugyfel(): BelongsTo
    {
        return $this->belongsTo(Ugyfel::class, 'dn_ugyfelkod', 'u_ugyfelkod');
    }
}
