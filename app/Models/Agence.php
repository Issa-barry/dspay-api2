<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'nom',
        'phone',
        'email',
        'adresse_id',
        'date_creation'
    ];

    // Relation avec l'adresse
    public function adresse()
    {
        return $this->belongsTo(Adresse::class);
    }
}
