<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taux extends Model
{
    use HasFactory;

    protected $fillable = [
        'montant_fixe',
        'pourcentage',
    ];

    protected $table = 'taux'; // Nom exact de la table
}
