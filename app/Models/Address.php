<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = ['name','street','city','PostalCode','user_id'];
}
