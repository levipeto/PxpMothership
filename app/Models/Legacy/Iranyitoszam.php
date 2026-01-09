<?php

namespace App\Models\Legacy;

class Iranyitoszam extends LegacyModel
{
    protected $table = 'iranyitoszam';

    protected $primaryKey = 'isz_id';

    public function getDeletedColumn(): ?string
    {
        return 'isz_torolve';
    }

    /**
     * Get the full location string.
     */
    public function getFullLocationAttribute(): string
    {
        return $this->isz_iranyito.' '.$this->isz_telepules;
    }
}
