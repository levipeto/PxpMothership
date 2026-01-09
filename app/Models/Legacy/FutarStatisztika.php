<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FutarStatisztika extends LegacyModel
{
    protected $table = 'futar_statisztika';

    protected $primaryKey = 'fs_id';

    public function getDeletedColumn(): ?string
    {
        return 'fs_torolve';
    }

    public function futar(): BelongsTo
    {
        return $this->belongsTo(Futar::class, 'fs_kod', 'f_kod');
    }
}
