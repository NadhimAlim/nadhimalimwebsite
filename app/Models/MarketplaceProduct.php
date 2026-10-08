<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceProduct extends Model
{
    protected $fillable = ['name', 'slug', 'category', 'description', 'price', 'stock', 'image', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'stock' => 'integer', 'is_active' => 'boolean'];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(MarketplaceOrder::class, 'marketplace_product_id');
    }
}
