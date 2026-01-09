<?php

namespace App\Models\Legacy;

class AppWaybillData extends LegacyModel
{
    protected $table = 'app_waybill_data';

    protected $primaryKey = 'awd_id';

    protected function casts(): array
    {
        return [
            'awd_timestamp' => 'datetime',
            'awd_scan_time' => 'datetime',
        ];
    }
}
