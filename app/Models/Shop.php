<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $table = 'shops';
    protected $fillable = ['shop_name', 'shop_image', 'lang', 'lat', 'category', 'shopkeeper_id'];
    // Accessor for full image URL
    // Accessor for full image URL
    // Accessor to override shop_image with full URL
    public function getShopImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
    public function shopkeeper()
    {
        return $this->belongsTo(ShopKeeper::class, 'shopkeeper_id');
    }
}
