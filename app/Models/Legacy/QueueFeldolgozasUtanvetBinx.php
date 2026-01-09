<?php

namespace App\Models\Legacy;

class QueueFeldolgozasUtanvetBinx extends LegacyModel
{
    protected $table = 'queue_feldolgozas_utanvet_binx';

    protected $primaryKey = 'qfub_id';

    protected function casts(): array
    {
        return [
            'qfub_datum_ido' => 'datetime',
            'qfub_adat' => 'json',
        ];
    }
}
