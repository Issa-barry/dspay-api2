<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TauxEchange;
use App\Models\Devise;

class Transfert extends Model
{
    use HasFactory;

    protected $fillable = [
        'devise_source_id',
        'devise_cible_id',
        'taux_echange_id',
        'montant',
        'montant_converti',
        'receveur_nom',
        'receveur_prenom',
        'receveur_phone',
        'expediteur_nom',
        'expediteur_prenom',
        'expediteur_phone',
        'expediteur_email',
        'quartier',
        'code',
    ];

    // Relation avec le modèle Devise (devise source)
    public function deviseSource()
    {
        return $this->belongsTo(Devise::class, 'devise_source_id');
    }

    // Relation avec le modèle Devise (devise cible)
    public function deviseCible()
    {
        return $this->belongsTo(Devise::class, 'devise_cible_id');
    }

    // Relation avec le modèle TauxEchange
    public function tauxEchange()
    {
        return $this->belongsTo(TauxEchange::class, 'taux_echange_id');
    }

    // Calcul du montant converti basé sur le taux d'échange
    public function calculerMontantConverti()
    {
        if ($this->tauxEchange) {
            return $this->montant * $this->tauxEchange->taux;
        }

        return 0; // Si aucun taux n'est trouvé, retourner 0
    }

     // Méthode pour générer un code unique pour chaque transfert
     public static function generateUniqueCode()
     {
         do {
             // Génère un code unique avec 6 caractères alphanumériques
             $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 2)) . rand(1000, 9999);
         } while (self::where('code', $code)->exists());  // Vérifie que le code n'existe pas déjà
 
         return $code;
     }

      // Pour gérer la génération automatique du code lors de la création d'un transfert
    protected static function booted()
    {
        static::creating(function ($transfert) {
            // Génère un code unique pour chaque transfert
            $transfert->code = self::generateUniqueCode();
        });
    }
}
