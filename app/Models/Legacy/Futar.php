<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Futar extends LegacyModel
{
    protected $table = 'futar';

    protected $primaryKey = 'f_id';

    public function getDeletedColumn(): ?string
    {
        return 'f_torolve';
    }

    protected $hidden = [
        'f_feljel',
        'f_feljel_plusz',
    ];

    public function statisztika(): HasOne
    {
        return $this->hasOne(FutarStatisztika::class, 'fs_kod', 'f_kod');
    }

    public function elszamolasok(): HasMany
    {
        return $this->hasMany(Futarelszamolas::class, 'fe_futar', 'f_kod');
    }

    public function rendelesek(): HasMany
    {
        return $this->hasMany(Futarrendeles::class, 'fr_futar', 'f_kod');
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class, 'futar_id', 'f_id');
    }

    public function kuldemenyekBe(): HasMany
    {
        return $this->hasMany(Kuldemeny::class, 'k_futar_be', 'f_kod');
    }

    public function kuldemenyekKi(): HasMany
    {
        return $this->hasMany(Kuldemeny::class, 'k_futar_ki', 'f_kod');
    }
}
