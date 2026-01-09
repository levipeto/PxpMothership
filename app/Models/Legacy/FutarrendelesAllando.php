<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FutarrendelesAllando extends LegacyModel
{
    protected $table = 'futarrendeles_allando';

    protected $primaryKey = 'fra_id';

    public function getDeletedColumn(): ?string
    {
        return 'fra_torolve';
    }

    protected function casts(): array
    {
        return [
            'fra_rogzitve_datum_ido' => 'datetime',
        ];
    }

    public function ugyfel(): BelongsTo
    {
        return $this->belongsTo(Ugyfel::class, 'fra_ugyfelkod', 'u_ugyfelkod');
    }

    public function futar(): BelongsTo
    {
        return $this->belongsTo(Futar::class, 'fra_futar', 'f_kod');
    }
}
