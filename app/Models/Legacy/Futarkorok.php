<?php

namespace App\Models\Legacy;

class Futarkorok extends LegacyModel
{
    protected $table = 'futarkorok';

    protected $primaryKey = 'fk_id';

    public function getDeletedColumn(): ?string
    {
        return 'fk_torolve';
    }
}
