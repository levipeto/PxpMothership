<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

abstract class LegacyModel extends Model
{
    /**
     * The database connection for legacy models.
     */
    protected $connection = 'legacy';

    /**
     * Disable Laravel's automatic timestamps.
     */
    public $timestamps = false;

    /**
     * Get the soft delete column name for this model.
     * Override in child classes if different.
     */
    public function getDeletedColumn(): ?string
    {
        return null;
    }

    /**
     * Scope to exclude soft-deleted records.
     */
    public function scopeNotDeleted($query)
    {
        $column = $this->getDeletedColumn();
        if ($column) {
            return $query->where($this->getTable().'.'.$column, 0);
        }

        return $query;
    }

    /**
     * Scope to only include soft-deleted records.
     */
    public function scopeOnlyDeleted($query)
    {
        $column = $this->getDeletedColumn();
        if ($column) {
            return $query->where($this->getTable().'.'.$column, 1);
        }

        return $query;
    }

    /**
     * Check if the model is soft-deleted.
     */
    public function isDeleted(): bool
    {
        $column = $this->getDeletedColumn();
        if ($column) {
            return (int) $this->getAttribute($column) === 1;
        }

        return false;
    }
}
