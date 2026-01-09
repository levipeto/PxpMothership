<?php

namespace App\Models\Legacy;

class QueuePxpMertSulyok extends LegacyModel
{
    protected $table = 'queue_pxp_mert_sulyok';

    protected $primaryKey = 'qpms_id';

    protected function casts(): array
    {
        return [
            'qpms_datum_ido' => 'datetime',
        ];
    }
}
