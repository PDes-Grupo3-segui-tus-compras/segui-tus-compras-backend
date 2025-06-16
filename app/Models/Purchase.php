<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Purchase extends Model {

    use HasFactory;
    protected $fillable = [
        'product_id', 'user_id', 'purchase_date', 'quantity', 'price',
    ];

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeTopPurchasedProducts($query, $limit = 5) {
        return $query->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('COUNT(*) as times_purchased'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->with('product:id,name,catalog_product_id,image')
            ->take($limit);
    }
}
