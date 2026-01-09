<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuldemenyNaplo extends LegacyModel
{
    protected $table = 'kuldemeny_naplo';

    protected $primaryKey = 'kn_id';

    protected function casts(): array
    {
        return [
            'kn_datum_ido' => 'datetime',
            'kn_adat' => 'json',
        ];
    }

    public function kuldemeny(): BelongsTo
    {
        return $this->belongsTo(Kuldemeny::class, 'kn_k_szam', 'k_szam');
    }
}
