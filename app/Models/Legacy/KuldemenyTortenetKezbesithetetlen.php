<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuldemenyTortenetKezbesithetetlen extends LegacyModel
{
    protected $table = 'kuldemeny_tortenet_kezbesithetetlen';

    protected $primaryKey = 'kt_id';

    protected function casts(): array
    {
        return [
            'kt_esemeny_datum_ido' => 'datetime',
            'kt_adat' => 'json',
        ];
    }

    public function kuldemeny(): BelongsTo
    {
        return $this->belongsTo(Kuldemeny::class, 'kt_k_szam', 'k_szam');
    }
}
