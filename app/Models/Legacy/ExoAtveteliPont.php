<?php

namespace App\Models\Legacy;

class ExoAtveteliPont extends LegacyModel
{
    protected $table = 'exo_atveteli_pontok';

    protected $primaryKey = 'eap_id';

    protected function casts(): array
    {
        return [
            'eap_latitude' => 'float',
            'eap_longitude' => 'float',
            'eap_opening' => 'json',
            'eap_exceptions' => 'json',
        ];
    }
}
