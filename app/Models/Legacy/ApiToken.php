<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends LegacyModel
{
    protected $table = 'api_tokens';

    protected $primaryKey = 'id';

    public $timestamps = true;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'scopes' => 'json',
        ];
    }

    protected $hidden = [
        'token',
    ];

    public function futar(): BelongsTo
    {
        return $this->belongsTo(Futar::class, 'futar_id', 'f_id');
    }
}
