<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Depok extends LegacyModel
{
    protected $table = 'depok';

    protected $primaryKey = 'dk_id';

    public function getDeletedColumn(): ?string
    {
        return 'dk_torolve';
    }

    protected function casts(): array
    {
        return [
            'dk_datum_ido' => 'datetime',
        ];
    }

    protected $hidden = [
        'dk_feljel',
        'dk_feljel_plusz',
    ];

    public function jaratok(): HasMany
    {
        return $this->hasMany(Depojarat::class, 'dj_depo', 'dk_depokod');
    }

    public function dijszabas(): HasMany
    {
        return $this->hasMany(DijszabasDepok::class, 'dd_depokod', 'dk_depokod');
    }
}
