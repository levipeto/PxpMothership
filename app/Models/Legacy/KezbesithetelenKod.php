<?php

namespace App\Models\Legacy;

class KezbesithetelenKod extends LegacyModel
{
    protected $table = 'kezbesithetetlen_kod';

    protected $primaryKey = 'kk_id';

    public function getDeletedColumn(): ?string
    {
        return 'kk_torolve';
    }
}
