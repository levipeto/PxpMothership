<?php

namespace App\Models\Legacy;

class DijszabasNemzetkoziKulondijak extends LegacyModel
{
    protected $table = 'dijszabas_nemzetkozi_kulondijak';

    protected $primaryKey = 'dnk_id';

    public function getDeletedColumn(): ?string
    {
        return 'dnk_torolve';
    }

    protected function casts(): array
    {
        return [
            'dnk_datum_ido' => 'datetime',
            'dnk_datum_tol' => 'date',
            'dnk_datum_ig' => 'date',
        ];
    }
}
