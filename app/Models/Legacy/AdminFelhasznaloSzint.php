<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminFelhasznaloSzint extends LegacyModel
{
    protected $table = 'admin_felhasznalo_szint';

    protected $primaryKey = 'afsz_id';

    public function felhasznalok(): HasMany
    {
        return $this->hasMany(AdminFelhasznalo::class, 'af_felhasznalo_szint', 'afsz_felhasznalo_szint');
    }
}
