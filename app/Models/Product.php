<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model {
    protected $fillable = [
        'catalog_product_id', 'name', 'image','price', 'short_description',
    ];

    public function purchases(): HasMany {
        return $this->hasMany(Purchase::class);
    }

    public function opinions(): HasMany {
        return $this->hasMany(Opinion::class);
    }

    public function favouritedBy(): BelongsToMany {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
