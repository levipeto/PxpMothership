<?php

namespace App\Models\Legacy;

class KshPostaiSzolgNegyedevesAdatai extends LegacyModel
{
    protected $table = 'ksh_postai_szolg_negyedeves_adatai';

    protected $primaryKey = 'ksh_pszna_id';

    protected function casts(): array
    {
        return [
            'ksh_pszna_adat' => 'json',
        ];
    }
}
