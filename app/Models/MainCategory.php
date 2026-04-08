<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainCategory extends Model
{
    protected  $table = 'main_categories';
    protected $fillable = ['name','urdu_name', 'image'];
    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'cat_id');
    }
    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'provider_skills');
    }
}
