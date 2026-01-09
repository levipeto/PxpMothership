<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\HasMany;

class KuldemenySzamlazasiAdatok extends LegacyModel
{
    protected $table = 'kuldemeny_szamlazasi_adatok';

    protected $primaryKey = 'ksza_id';

    public function kuldemenyek(): HasMany
    {
        return $this->hasMany(Kuldemeny::class, 'k_szamlazasi_adatok_id', 'ksza_id');
    }
}
