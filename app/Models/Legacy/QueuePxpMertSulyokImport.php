<?php

namespace App\Models\Legacy;

class QueuePxpMertSulyokImport extends LegacyModel
{
    protected $table = 'queue_pxp_mert_sulyok_import';

    protected $primaryKey = 'qpmsi_id';

    protected function casts(): array
    {
        return [
            'qpmsi_datum_ido' => 'datetime',
        ];
    }
}
