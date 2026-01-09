<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Ugyfel extends LegacyModel
{
    protected $table = 'ugyfel';

    protected $primaryKey = 'u_id';

    public function getDeletedColumn(): ?string
    {
        return 'u_torolve';
    }

    protected function casts(): array
    {
        return [
            'u_reg_datum_ido' => 'datetime',
            'u_beallitas' => 'json',
        ];
    }

    public function felhasznalok(): HasMany
    {
        return $this->hasMany(UgyfelFelhasznalo::class, 'uf_ugyfelkod', 'u_ugyfelkod');
    }

    public function cimek(): HasMany
    {
        return $this->hasMany(UgyfelCim::class, 'uc_ugyfelkod', 'u_ugyfelkod');
    }

    public function kuldemenyek(): HasMany
    {
        return $this->hasMany(Kuldemeny::class, 'k_ugyfelkod', 'u_ugyfelkod');
    }

    public function futarrendelesek(): HasMany
    {
        return $this->hasMany(Futarrendeles::class, 'fr_ugyfelkod', 'u_ugyfelkod');
    }

    public function dijszabasBelfold(): HasMany
    {
        return $this->hasMany(DijszabasBelfold::class, 'db_ugyfelkod', 'u_ugyfelkod');
    }

    public function dijszabasNemzetkozi(): HasMany
    {
        return $this->hasMany(DijszabasNemzetkozi::class, 'dn_ugyfelkod', 'u_ugyfelkod');
    }
}
