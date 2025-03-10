<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use App\Mail\TransfertNotification;
use App\Models\Devise;
use App\Models\Facture;
use App\Models\TauxEchange;
use App\Models\Transfert;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TransfertEnvoieController extends Controller
{
    /**
     * Fonction pour centraliser les réponses JSON
     */
    protected function responseJson($success, $message, $data = null, $statusCode = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    } 

    /**
     * Créer un transfert de devise.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response 
     */
    public function store(Request $request)
    {
        //Validation des entrées
        $validated = Validator::make($request->all(), [
            'devise_source_id' => 'required|exists:devises,id',
            'devise_cible_id' => 'required|exists:devises,id',
            'montant_expediteur' => 'required|numeric|min:1',
            'frais' => 'required|integer|min:0',
            'quartier' => 'nullable|string',
            'receveur_nom' => 'required|string|max:255',
            'receveur_prenom' => 'required|string|max:255',
            'receveur_phone' => 'required|string|max:20',
            'expediteur_nom' => 'required|string|max:255',
            'expediteur_prenom' => 'required|string|max:255',
            'expediteur_phone' => 'required|string|max:20',
            'expediteur_email' => 'required|email|max:255',
        ]);

        if ($validated->fails()) {
            return $this->responseJson(false, 'Validation échouée.', $validated->errors(), 422);
        }

        try {
            //Récupération des devises et vérification
            $deviseSource = $this->getDevise($request->devise_source_id);
            $deviseCible = $this->getDevise($request->devise_cible_id);

            if (!$deviseSource || !$deviseCible) {
                return $this->responseJson(false, 'Devise source ou cible introuvable.', null, 404);
            }

            //Récupération du taux de change
            $tauxEchange = $this->getTauxEchange($request->devise_source_id, $request->devise_cible_id);
            if (!$tauxEchange) {
                return $this->responseJson(false, 'Taux de change invalide.', null, 400);
            }

            //Calcul du montant converti et total
            $montantConverti = $request->montant_expediteur * $tauxEchange->taux;
            $total = $request->montant_expediteur + $request->frais;

            //Création du transfert
            $transfert = Transfert::create([
                'devise_source_id' => $request->devise_source_id,
                'devise_cible_id' => $request->devise_cible_id,
                'montant_expediteur' => $request->montant_expediteur,
                'montant_receveur' => $montantConverti,
                'frais' => $request->frais,
                'total' => $total,
                'quartier' => $request->quartier,
                'receveur_nom' => $request->receveur_nom,
                'receveur_prenom' => $request->receveur_prenom,
                'receveur_phone' => $request->receveur_phone,
                'expediteur_nom' => $request->expediteur_nom,
                'expediteur_prenom' => $request->expediteur_prenom,
                'expediteur_phone' => $request->expediteur_phone,
                'expediteur_email' => $request->expediteur_email,
                'taux_echange_id' => $tauxEchange->id,
                'statut' => 'en_cours',
                'code' => Transfert::generateUniqueCode(),
            ]);

            //Création de la facture
            $this->createFacture($transfert);

            //Envoi de l'email de confirmation (seulement si l'email est défini)
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
     * Récupérer une devise par son ID.
     */
    private function getDevise($id)
    {
        return Devise::find($id);
    }

    /**
     * Récupérer le taux d'échange entre deux devises.
     */
    private function getTauxEchange($deviseSourceId, $deviseCibleId)
    {
        return TauxEchange::where('devise_source_id', $deviseSourceId)
            ->where('devise_cible_id', $deviseCibleId)
            ->first();
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
