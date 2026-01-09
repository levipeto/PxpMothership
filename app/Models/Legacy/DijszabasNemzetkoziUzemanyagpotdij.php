<?php

namespace App\Models\Legacy;

class DijszabasNemzetkoziUzemanyagpotdij extends LegacyModel
{
    protected $table = 'dijszabas_nemzetkozi_uzemanyagpotdij';

    protected $primaryKey = 'dnu_id';

    public function getDeletedColumn(): ?string
    {
        return 'dnu_torolve';
    }

    protected function casts(): array
    {
        return [
            'dnu_datum_ido' => 'datetime',
            'dnu_datum_tol' => 'date',
            'dnu_datum_ig' => 'date',
        ];
    }
}
