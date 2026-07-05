<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MainCategory extends Model
{
    protected  $table = 'main_categories';
    protected $fillable = ['name','urdu_name', 'image'];

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

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'cat_id');
    }
    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'provider_skills');
    }
}
