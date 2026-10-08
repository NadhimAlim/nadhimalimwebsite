<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkTask extends Model
{
    protected $fillable = ['title', 'client', 'description', 'scheduled_at', 'deadline_at', 'status', 'priority', 'progress_notes', 'project_value', 'amount_paid', 'payment_status'];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'deadline_at' => 'datetime',
            'project_value' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ProjectPayment::class);
    }
}
