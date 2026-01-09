<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kuldemeny extends LegacyModel
{
    protected $table = 'kuldemeny';

    protected $primaryKey = 'k_id';

    public function getDeletedColumn(): ?string
    {
        return 'k_torolve';
    }

    protected function casts(): array
    {
        return [
            'k_rogzitve_datum_ido' => 'datetime',
            'k_felvetel_datum' => 'date',
            'k_felveve_datum_ido' => 'datetime',
            'k_beerkezes_datum_ido' => 'datetime',
            'k_kiszallitas_datum' => 'date',
            'k_kiszallitva_datum_ido' => 'datetime',
            'k_kezbesithetetlen_datum_ido' => 'datetime',
            'k_uv_vissza_datum' => 'date',
            'k_utanvet' => 'decimal:2',
            'k_utanvet_arfolyam' => 'decimal:4',
            'k_csomagok' => 'json',
        ];
    }

    // Status constants
    public const STATUS_REGISTERED_PORTAL = 10;

    public const STATUS_REGISTERED_IMPORT = 11;

    public const STATUS_REGISTERED_API = 12;

    public const STATUS_LABEL_PRINTED_PORTAL = 20;

    public const STATUS_LABEL_PRINTED_API = 22;

    public const STATUS_LABEL_SELF_CREATED = 23;

    public const STATUS_READY_PORTAL = 30;

    public const STATUS_READY_API = 32;

    public const STATUS_PICKUP_IN_PROGRESS = 40;

    public const STATUS_PICKUP_FAILED = 41;

    public const STATUS_PICKED_UP = 42;

    public const STATUS_AT_CENTRAL_WAREHOUSE = 50;

    public const STATUS_REMAINS_AT_CENTRAL = 59;

    public const STATUS_LEFT_CENTRAL = 60;

    public const STATUS_FORWARDED_TO_DEPOT = 61;

    public const STATUS_FORWARDED_TO_EXO = 62;

    public const STATUS_FORWARDED_TO_PICKUP_POINT = 63;

    public const STATUS_AT_DEPOT = 70;

    public const STATUS_REMAINS_AT_DEPOT = 71;

    public const STATUS_WRONG_DEPOT = 72;

    public const STATUS_WITH_DELIVERY_COURIER = 80;

    public const STATUS_RETURNED_TO_SENDER = 81;

    public const STATUS_RESENT = 82;

    public const STATUS_DELIVERED = 90;

    public const STATUS_RETURNED_DELIVERED = 91;

    public const STATUS_DELIVERED_NEW_NUMBER = 92;

    public const STATUS_DELETED = 100;

    public const STATUS_GOODS_DAMAGE = 110;

    // Service type constants
    public const SERVICE_INSTANT = 0;

    public const SERVICE_SAME_DAY = 1;

    public const SERVICE_24H = 2;

    public const SERVICE_12H = 3;

    public const SERVICE_10H = 4;

    public const SERVICE_08H = 5;

    public const SERVICE_SATURDAY = 6;

    public const SERVICE_OVERNIGHT = 7;

    public const SERVICE_AIR_INTERNATIONAL = 8;

    public const SERVICE_ROAD_INTERNATIONAL = 9;

    public const SERVICE_DROP_TO_STORE = 10;

    // Cost bearer constants
    public const COST_CLIENT_TRANSFER = 0;

    public const COST_CLIENT_COD = 1;

    public const COST_SENDER_CASH = 2;

    public const COST_SENDER_CARD = 3;

    public const COST_SENDER_TRANSFER = 4;

    public const COST_SENDER_COD = 5;

    public const COST_RECIPIENT_CASH = 6;

    public const COST_RECIPIENT_CARD = 7;

    public const COST_RECIPIENT_TRANSFER = 8;

    public const COST_THIRD_PARTY = 9;

    public const COST_FREE = 10;

    /**
     * Get the computed/effective status code.
     * Takes into account soft delete flags and damage flags.
     */
    public function getEffectiveStatusAttribute(): int
    {
        $status = (int) $this->k_statusz;

        if ((int) $this->k_torolve === 1) {
            return self::STATUS_DELETED;
        }

        if ((int) $this->k_vissza === 1 && $status < 90) {
            return self::STATUS_RETURNED_TO_SENDER;
        }

        if ((int) $this->k_ujrakuldve === 1 && $status < 90) {
            return self::STATUS_RESENT;
        }

        if ((int) $this->k_arukar_serult === 1 ||
            (int) $this->k_arukar_megsemmisult === 1 ||
            (int) $this->k_arukar_elveszett === 1) {
            return self::STATUS_GOODS_DAMAGE;
        }

        return $status;
    }

    /**
     * Check if shipment is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->effective_status >= 90 && $this->effective_status < 100;
    }

    /**
     * Check if shipment is in transit.
     */
    public function isInTransit(): bool
    {
        return $this->effective_status >= 40 && $this->effective_status < 90;
    }

    /**
     * Check if shipment can be modified.
     */
    public function canBeModified(): bool
    {
        return $this->k_statusz <= 29 && ! $this->isDeleted();
    }

    // Relationships

    public function ugyfel(): BelongsTo
    {
        return $this->belongsTo(Ugyfel::class, 'k_ugyfelkod', 'u_ugyfelkod');
    }

    public function koltsegviseloUgyfel(): BelongsTo
    {
        return $this->belongsTo(Ugyfel::class, 'k_koltsegviselo_ugyfelkod', 'u_ugyfelkod');
    }

    public function futarBe(): BelongsTo
    {
        return $this->belongsTo(Futar::class, 'k_futar_be', 'f_kod');
    }

    public function futarKi(): BelongsTo
    {
        return $this->belongsTo(Futar::class, 'k_futar_ki', 'f_kod');
    }

    public function szamlazasiAdatok(): BelongsTo
    {
        return $this->belongsTo(KuldemenySzamlazasiAdatok::class, 'k_szamlazasi_adatok_id', 'ksza_id');
    }

    public function naplo(): HasMany
    {
        return $this->hasMany(KuldemenyNaplo::class, 'kn_k_szam', 'k_szam');
    }

    public function tortenetUgyfel(): HasMany
    {
        return $this->hasMany(KuldemenyTortenetUgyfel::class, 'kt_k_szam', 'k_szam');
    }

    public function tortenetExo(): HasMany
    {
        return $this->hasMany(KuldemenyTortenetExo::class, 'kt_k_szam', 'k_szam');
    }

    public function kezbesithetelenKod(): BelongsTo
    {
        return $this->belongsTo(KezbesithetelenKod::class, 'k_kezbesithetetlen_kod', 'kk_kod');
    }

    public function exoAtveteliPont(): BelongsTo
    {
        return $this->belongsTo(ExoAtveteliPont::class, 'k_uc_atveteli_pont_code', 'eap_code');
    }

    // Scopes

    public function scopeStatus($query, int $status)
    {
        return $query->where('k_statusz', $status);
    }

    public function scopeDelivered($query)
    {
        return $query->whereBetween('k_statusz', [90, 99]);
    }

    public function scopeInTransit($query)
    {
        return $query->whereBetween('k_statusz', [40, 89]);
    }

    public function scopeForCustomer($query, string $ugyfelkod)
    {
        return $query->where('k_ugyfelkod', $ugyfelkod);
    }

    public function scopeByShipmentNumber($query, string $szam)
    {
        return $query->where('k_szam', $szam);
    }
}
