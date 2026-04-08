<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $table = 'sub_categories';
    protected $fillable = ['name', 'urdu_name', 'image', 'cat_id'];



    public function mainCategory()
    {
        return $this->belongsTo(MainCategory::class, 'cat_id');
    }
}
