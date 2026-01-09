<?php

namespace App\Models\Legacy;

class UnnepnapokMunkanapok extends LegacyModel
{
    protected $table = 'unnepnapok_munkanapok';

    protected $primaryKey = 'um_id';

    protected function casts(): array
    {
        return [
            'um_datum' => 'date',
        ];
    }
}
