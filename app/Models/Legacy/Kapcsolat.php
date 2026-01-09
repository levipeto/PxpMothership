<?php

namespace App\Models\Legacy;

class Kapcsolat extends LegacyModel
{
    protected $table = 'kapcsolat';

    protected $primaryKey = 'k_id';

    protected function casts(): array
    {
        return [
            'k_datum_ido' => 'datetime',
        ];
    }
}
