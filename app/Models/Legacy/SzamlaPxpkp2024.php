<?php

namespace App\Models\Legacy;

class SzamlaPxpkp2024 extends LegacyModel
{
    protected $table = 'szamla_pxpkp_2024';

    protected $primaryKey = 'sp_id';

    protected function casts(): array
    {
        return [
            'sp_datum' => 'date',
            'sp_letrehozva' => 'datetime',
        ];
    }
}
