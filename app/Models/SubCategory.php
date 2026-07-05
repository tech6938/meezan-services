<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubCategory extends Model
{
    protected $table = 'sub_categories';
    protected $fillable = ['name', 'urdu_name', 'image', 'cat_id'];

    public function getImageAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        return url(Storage::url($value));
    }

    public function mainCategory()
    {
        return $this->belongsTo(MainCategory::class, 'cat_id');
    }
}
