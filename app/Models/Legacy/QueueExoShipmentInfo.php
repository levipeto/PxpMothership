<?php

namespace App\Models\Legacy;

class QueueExoShipmentInfo extends LegacyModel
{
    protected $table = 'queue_exo_shipment_info';

    protected $primaryKey = 'qesi_id';

    protected function casts(): array
    {
        return [
            'qesi_datum_ido' => 'datetime',
            'qesi_adat' => 'json',
        ];
    }
}
