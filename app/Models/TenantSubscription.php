<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSubscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'next_plan_id',
        'status',
        'started_at',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'downgrade_effective_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'trial_ends_at' => 'datetime',
            'downgrade_effective_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function nextPlan()
    {
        return $this->belongsTo(Plan::class, 'next_plan_id');
    }
}

