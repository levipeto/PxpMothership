<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cegadatbazis extends LegacyModel
{
    protected $table = 'cegadatbazis';

    protected $primaryKey = 'c_id';

    public function getDeletedColumn(): ?string
    {
        return 'c_torolve';
    }

    protected function casts(): array
    {
        return [
            'c_modositas_datum_ido' => 'datetime',
        ];
    }

    public function uzletkoto(): BelongsTo
    {
        return $this->belongsTo(AdminFelhasznalo::class, 'c_uzletkoto_af_id', 'af_id');
    }

    public function bejegyzesek(): HasMany
    {
        return $this->hasMany(CegadatbazisBejegyzes::class, 'cb_c_id', 'c_id');
    }

    public function ugyfel(): BelongsTo
    {
        return $this->belongsTo(Ugyfel::class, 'c_ugyfelkod', 'u_ugyfelkod');
    }
}
