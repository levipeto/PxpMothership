<?php

namespace App\Models\Legacy;

class DijszabasNemzetkoziArfolyam extends LegacyModel
{
    protected $table = 'dijszabas_nemzetkozi_arfolyam';

    protected $primaryKey = 'dna_id';

    protected function casts(): array
    {
        return [
            'dna_datum' => 'date',
            'dna_letrehozva' => 'datetime',
        ];
    }
}
