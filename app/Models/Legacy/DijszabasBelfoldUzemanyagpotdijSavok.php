<?php

namespace App\Models\Legacy;

class DijszabasBelfoldUzemanyagpotdijSavok extends LegacyModel
{
    protected $table = 'dijszabas_belfold_uzemanyagpotdij_savok';

    protected $primaryKey = 'dbus_id';

    public function getDeletedColumn(): ?string
    {
        return 'dbus_torolve';
    }

    protected function casts(): array
    {
        return [
            'dbus_datum_ido' => 'datetime',
            'dbus_datum_tol' => 'date',
            'dbus_datum_ig' => 'date',
        ];
    }
}
