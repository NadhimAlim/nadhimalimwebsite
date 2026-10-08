<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceOrder extends Model
{
    protected $fillable = [
        'marketplace_product_id', 'public_token', 'order_id', 'product_name', 'unit_price', 'quantity', 'total_amount',
        'customer_name', 'customer_email', 'customer_phone', 'shipping_address', 'status', 'payment_status',
        'snap_token', 'redirect_url', 'transaction_id', 'payment_type', 'notification_payload', 'paid_at',
    ];

    protected function casts(): array
    {
        return ['notification_payload' => 'array', 'paid_at' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MarketplaceProduct::class, 'marketplace_product_id');
    }
}
