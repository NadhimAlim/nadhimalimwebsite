<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPayment extends Model
{
    protected $fillable = [
        'work_task_id', 'public_token', 'order_id', 'customer_name', 'customer_email',
        'gross_amount', 'snap_token', 'redirect_url', 'transaction_id', 'payment_type',
        'status', 'notification_payload', 'paid_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return ['notification_payload' => 'array', 'paid_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    public function workTask(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class);
    }
}
