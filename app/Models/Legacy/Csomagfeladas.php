<?php

namespace App\Models\Legacy;

class Csomagfeladas extends LegacyModel
{
    protected $table = 'csomagfeladas';

    protected $primaryKey = 'cs_id';

    protected function casts(): array
    {
        return [
            'cs_datum_ido' => 'datetime',
            'cs_adatok' => 'json',
        ];
    }
}
