<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UgyfelCim extends LegacyModel
{
    protected $table = 'ugyfel_cim';

    protected $primaryKey = 'uc_id';

    public function getDeletedColumn(): ?string
    {
        return 'uc_torolve';
    }

    public function ugyfel(): BelongsTo
    {
        return $this->belongsTo(Ugyfel::class, 'uc_ugyfelkod', 'u_ugyfelkod');
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->uc_cim_iranyito,
            $this->uc_cim_telepules,
            $this->uc_cim_kozterulet,
        ]));
    }
}
