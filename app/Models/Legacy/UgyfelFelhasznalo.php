<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UgyfelFelhasznalo extends LegacyModel
{
    protected $table = 'ugyfel_felhasznalo';

    protected $primaryKey = 'uf_id';

    public function getDeletedColumn(): ?string
    {
        return 'uf_torolve';
    }

    protected function casts(): array
    {
        return [
            'uf_reg_datum_ido' => 'datetime',
            'uf_beallitas' => 'json',
        ];
    }

    protected $hidden = [
        'uf_feljel',
    ];

    public function ugyfel(): BelongsTo
    {
        return $this->belongsTo(Ugyfel::class, 'uf_ugyfelkod', 'u_ugyfelkod');
    }

    public function alapCim(): BelongsTo
    {
        return $this->belongsTo(UgyfelCim::class, 'uf_alap_uc_id', 'uc_id');
    }

    public function apiCredentials(): HasOne
    {
        return $this->hasOne(UgyfelFelhasznaloApi::class, 'ufa_ugyfelkod', 'uf_ugyfelkod');
    }
}
