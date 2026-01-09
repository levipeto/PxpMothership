<?php

namespace App\Models\Legacy;

class DijszabasBelfoldUzemanyagpotdij extends LegacyModel
{
    protected $table = 'dijszabas_belfold_uzemanyagpotdij';

    protected $primaryKey = 'dbu_id';

    public function getDeletedColumn(): ?string
    {
        return 'dbu_torolve';
    }

    protected function casts(): array
    {
        return [
            'dbu_datum_ido' => 'datetime',
            'dbu_datum_tol' => 'date',
            'dbu_datum_ig' => 'date',
        ];
    }
}
