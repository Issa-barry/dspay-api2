<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use App\Mail\TransfertNotification;
use App\Models\Devise;
use App\Models\Facture;
use App\Models\TauxEchange;
use App\Models\Transfert;
use App\Traits\JsonResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TransfertEnvoieController extends Controller
{
     use JsonResponseTrait;

    /**
     * Créer un transfert de devise en utilisant un taux déjà existant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response 
     */
    public function store(Request $request) 
    {
        // Validation des entrées
        $validated = Validator::make($request->all(), [
            'taux_echange_id' => 'required|exists:taux_echanges,id',
            'montant_expediteur' => 'required|numeric|min:1',
            'frais' => 'required|integer|min:0',
            'quartier' => 'nullable|string',
            'receveur_nom_complet' => 'required|string|max:255', 
            'receveur_phone' => 'required|string|max:20',
            'expediteur_nom_complet' => 'required|string|max:255', 
            'expediteur_phone' => 'required|string|max:20',
            'expediteur_email' => 'required|email|max:255',
        ]);

        if ($validated->fails()) {
            return $this->responseJson(false, 'Validation échouée.', $validated->errors(), 422);
        }

        try {
            // Récupération du taux de change et des devises associées
            $tauxEchange = TauxEchange::find($request->taux_echange_id);
            if (!$tauxEchange) {
                return $this->responseJson(false, 'Taux de change introuvable.', null, 404);
            }

            $deviseSource = Devise::find($tauxEchange->devise_source_id);
            $deviseCible = Devise::find($tauxEchange->devise_cible_id);

            if (!$deviseSource || !$deviseCible) {
                return $this->responseJson(false, 'Les devises associées à ce taux sont introuvables.', null, 404);
            }

            // Calcul du montant converti et total
            $montantConverti = $request->montant_expediteur * $tauxEchange->taux;
            $total = $request->montant_expediteur + $request->frais;

            // Création du transfert
            $transfert = Transfert::create([
                'devise_source_id' => $deviseSource->id,
                'devise_cible_id' => $deviseCible->id,
                'montant_expediteur' => $request->montant_expediteur,
                'montant_receveur' => $montantConverti,
                'frais' => $request->frais,
                'total' => $total,
                'quartier' => $request->quartier,
                'receveur_nom_complet' => $request->receveur_nom_complet,
                'receveur_phone' => $request->receveur_phone,
                'expediteur_nom_complet' => $request->expediteur_nom_complet, 
                'expediteur_phone' => $request->expediteur_phone,
                'expediteur_email' => $request->expediteur_email,
                'taux_echange_id' => $tauxEchange->id,
                'statut' => 'en_cours',
                'code' => Transfert::generateUniqueCode(),
            ]);

            // Création de la facture
            $this->createFacture($transfert);

            // Envoi de l'email de confirmation (seulement si l'email est défini)
            if (!empty($transfert->expediteur_email)) {
                Mail::to($transfert->expediteur_email)->send(new TransfertNotification($transfert));
            }

            return $this->responseJson(true, 'Transfert effectué avec succès.', $transfert, 201);
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la création du transfert.', [
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une facture pour un transfert donné.
     */
    private function createFacture(Transfert $transfert)
    {
        return Facture::create([
            'transfert_id' => $transfert->id,
            'type' => 'transfert',
            'statut' => 'brouillon',
            'envoye' => false,
            'nom_societe' => 'FELLO',
            'adresse_societe' => '5 allé du Foehn Ostwald 67540, Strasbourg.',
            'phone_societe' => 'Numéro de téléphone de la société',
            'email_societe' => 'contact@societe.com',
            'total' => $transfert->total,
            'montant_du' => $transfert->total
        ]);
    }
}
