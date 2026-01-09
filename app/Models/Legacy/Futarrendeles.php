<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Futarrendeles extends LegacyModel
{
    protected $table = 'futarrendeles';

    protected $primaryKey = 'fr_id';

    public function getDeletedColumn(): ?string
    {
        return 'fr_torolve';
    }

    protected function casts(): array
    {
        return [
            'fr_rogzitve_datum_ido' => 'datetime',
            'fr_felvetel_datum' => 'date',
        ];
    }

    public function ugyfel(): BelongsTo
    {
        return $this->belongsTo(Ugyfel::class, 'fr_ugyfelkod', 'u_ugyfelkod');
    }

    public function futar(): BelongsTo
    {
        return $this->belongsTo(Futar::class, 'fr_futar', 'f_kod');
    }

    public function tortenet(): HasMany
    {
        return $this->hasMany(FutarrendelesTortenet::class, 'frt_fr_id', 'fr_id');
    }
}
