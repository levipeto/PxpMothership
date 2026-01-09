<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends LegacyModel
{
    protected $table = 'admin';

    protected $primaryKey = 'a_id';

    public function getDeletedColumn(): ?string
    {
        return 'a_torolve';
    }

    protected function casts(): array
    {
        return [
            'a_datum_ido' => 'datetime',
            'a_nav' => 'json',
        ];
    }

    public function felhasznalok(): HasMany
    {
        return $this->hasMany(AdminFelhasznalo::class, 'af_admin_kod', 'a_kod');
    }

    public function ugyfelek(): HasMany
    {
        return $this->hasMany(Ugyfel::class, 'u_admin_kod', 'a_kod');
    }
}
