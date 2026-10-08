<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'category', 'description', 'image', 'link', 'sort_order'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
