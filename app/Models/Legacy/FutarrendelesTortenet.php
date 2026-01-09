<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FutarrendelesTortenet extends LegacyModel
{
    protected $table = 'futarrendeles_tortenet';

    protected $primaryKey = 'frt_id';

    protected function casts(): array
    {
        return [
            'frt_datum_ido' => 'datetime',
            'frt_adat' => 'json',
        ];
    }

    public function futarrendeles(): BelongsTo
    {
        return $this->belongsTo(Futarrendeles::class, 'frt_fr_id', 'fr_id');
    }

    public function kod(): BelongsTo
    {
        return $this->belongsTo(FutarrendelesTortenetKod::class, 'frt_kod', 'frtk_kod');
    }
}
