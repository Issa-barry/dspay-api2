<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    use HasFactory;

    protected $fillable = [ 
        // 'reference',
        'nom_agence',
        'phone',
        'email',
        'statut',
        'date_creation',
        'adresse_id',
    ];

    public function adresse()
    {
        return $this->belongsTo(Adresse::class);
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->reference = self::generateUniqueReference();
        });
    }

    public static function generateUniqueReference()
    {
        do {
            $reference = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 2)) . rand(10, 99) . rand(0, 9);
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }

}
