<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
       ];

     public function setNomAttribute($value)
     {
         $this->attributes['name'] = ucfirst(strtolower($value));
     }
}
