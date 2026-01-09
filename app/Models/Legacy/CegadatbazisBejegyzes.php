<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CegadatbazisBejegyzes extends LegacyModel
{
    protected $table = 'cegadatbazis_bejegyzes';

    protected $primaryKey = 'cb_id';

    public function getDeletedColumn(): ?string
    {
        return 'cb_torolve';
    }

    protected function casts(): array
    {
        return [
            'cb_datum_ido' => 'datetime',
        ];
    }

    public function cegadatbazis(): BelongsTo
    {
        return $this->belongsTo(Cegadatbazis::class, 'cb_c_id', 'c_id');
    }

    public function uzletkoto(): BelongsTo
    {
        return $this->belongsTo(AdminFelhasznalo::class, 'cb_uzletkoto_af_id', 'af_id');
    }
}
