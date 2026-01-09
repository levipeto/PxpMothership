<?php

namespace App\Models\Legacy;

class KshPostaiSzolgaltatasEsEszkozei extends LegacyModel
{
    protected $table = 'ksh_a_postai_szolgaltatas_es_eszkozei';

    protected $primaryKey = 'kshapszee_id';

    protected function casts(): array
    {
        return [
            'kshapszee_datum_ido' => 'datetime',
        ];
    }
}
