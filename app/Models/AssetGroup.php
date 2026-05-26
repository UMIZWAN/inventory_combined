<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetGroup extends Model
{
    protected $table = 'inventory_asset_groups';

    protected $fillable = [
        'name',
        'description',
        'lifespan_from',
        'lifespan_to',
        'service_interval_from',
        'service_interval_to',
        'branc_pic',
        'remark',
        'require_asset_no',
        'is_deleted',
        'logs',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'require_asset_no' => 'boolean',
        'lifespan_from' => 'integer',
        'lifespan_to' => 'integer',
        'service_interval_from' => 'integer',
        'service_interval_to' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Get formatted lifespan display
     * - Both null: "Permanent"
     * - Only from: "X Years" or "X Months"
     * - Both values: "X - Y Years" or "X Months - Y Years"
     */
    public function getLifespanDisplayAttribute(): string
    {
        if (is_null($this->lifespan_from) && is_null($this->lifespan_to)) {
            return 'Permanent';
        }

        if (is_null($this->lifespan_to)) {
            return $this->formatDuration($this->lifespan_from);
        }

        if (is_null($this->lifespan_from)) {
            return $this->formatDuration($this->lifespan_to);
        }

        return $this->formatDuration($this->lifespan_from) . ' - ' . $this->formatDuration($this->lifespan_to);
    }

    /**
     * Get formatted service interval display
     * - Both null: "Case by case"
     * - Only from: "X Years" or "X Months"
     * - Both values: "X - Y Years" or "X Months - Y Years"
     */
    public function getServiceIntervalDisplayAttribute(): string
    {
        if (is_null($this->service_interval_from) && is_null($this->service_interval_to)) {
            return 'Case by case';
        }

        if (is_null($this->service_interval_to)) {
            return $this->formatDuration($this->service_interval_from);
        }

        if (is_null($this->service_interval_from)) {
            return $this->formatDuration($this->service_interval_to);
        }

        return $this->formatDuration($this->service_interval_from) . ' - ' . $this->formatDuration($this->service_interval_to);
    }

    /**
     * Format months into human-readable duration
     * Converts months to years if >= 12 months
     */
    private function formatDuration(int $months): string
    {
        if ($months >= 12 && $months % 12 === 0) {
            $years = $months / 12;
            return $years . ' ' . ($years === 1 ? 'Year' : 'Years');
        }

        if ($months >= 12) {
            $years = floor($months / 12);
            $remainingMonths = $months % 12;
            if ($remainingMonths === 0) {
                return $years . ' ' . ($years === 1 ? 'Year' : 'Years');
            }
            return $years . ' ' . ($years === 1 ? 'Year' : 'Years') . ' ' . $remainingMonths . ' ' . ($remainingMonths === 1 ? 'Month' : 'Months');
        }

        return $months . ' ' . ($months === 1 ? 'Month' : 'Months');
    }
}
