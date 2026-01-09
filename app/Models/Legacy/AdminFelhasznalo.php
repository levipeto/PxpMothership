<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminFelhasznalo extends LegacyModel
{
    protected $table = 'admin_felhasznalo';

    protected $primaryKey = 'af_id';

    public function getDeletedColumn(): ?string
    {
        return 'af_torolve';
    }

    protected function casts(): array
    {
        return [
            'af_reg_datum_ido' => 'datetime',
        ];
    }

    protected $hidden = [
        'af_feljel',
        'af_feljel_plusz',
    ];

    public function szint(): BelongsTo
    {
        return $this->belongsTo(AdminFelhasznaloSzint::class, 'af_felhasznalo_szint', 'afsz_felhasznalo_szint');
    }
}
