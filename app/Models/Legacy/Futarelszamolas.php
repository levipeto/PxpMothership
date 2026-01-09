<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Futarelszamolas extends LegacyModel
{
    protected $table = 'futarelszamolas';

    protected $primaryKey = 'fe_id';

    protected function casts(): array
    {
        return [
            'fe_datum_ido' => 'datetime',
        ];
    }

    public function futar(): BelongsTo
    {
        return $this->belongsTo(Futar::class, 'fe_futar', 'f_kod');
    }
}
