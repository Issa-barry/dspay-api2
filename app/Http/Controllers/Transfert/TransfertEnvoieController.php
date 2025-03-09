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
use Mail;
use Validator;

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
         
        $validated = Validator::make($request->all(), [
            'devise_source_id' => 'required|exists:devises,id',
            'devise_cible_id' => 'required|exists:devises,id',
            'montant' => 'required|numeric|min:0',
            'quartier' => 'string',
            'receveur_nom' => 'required|string',
            'receveur_prenom' => 'required|string',
            'receveur_phone' => 'required|string',
            'expediteur_nom' => 'required|string',
            'expediteur_prenom' => 'required|string',
            'expediteur_phone' => 'required|string',
            'expediteur_email' => 'required|email',
        ]);

        if ($validated->fails()) {
            return $this->responseJson(false, 'Validation échouée.', $validated->errors(), 422);
        }

        try {
             
            $deviseSource = Devise::find($request->devise_source_id);
            $deviseCible = Devise::find($request->devise_cible_id);
 
            if (!$deviseSource || !$deviseCible) {
                return $this->responseJson(false, 'Devise source ou cible introuvable.', null, 404);
            }

           
            $tauxEchange = TauxEchange::where('devise_source_id', $request->devise_source_id)
                ->where('devise_cible_id', $request->devise_cible_id)
                ->first();

            if (!$tauxEchange) {
                return $this->responseJson(false, 'Taux de change introuvable pour ces devises.', null, 404);
            }

           
            if (is_null($tauxEchange->taux) || $tauxEchange->taux <= 0) {
                return $this->responseJson(false, 'Le taux de change est invalide.', null, 400);
            }

            // Calculer le montant converti
            $montantConverti = $request->montant * $tauxEchange->taux;

            
            $transfert = Transfert::create([
                'devise_source_id' => $request->devise_source_id,
                'devise_cible_id' => $request->devise_cible_id,
                'montant' => $request->montant,
                'montant_converti' => $montantConverti,
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
                'code' => (string) \Str::uuid(), // Générer un UUID comme code
            ]);

            // Créer la facture associée au transfert
            $facture = Facture::create([
                'transfert_id' => $transfert->id,
                'type' => 'transfert',
                'statut' => 'brouillon',
                'envoye' => false,
                'nom_societe' => 'FELLO', 
                'adresse_societe' => '5 allé du Foehn Ostwald 67540, Strasbourg.',
                'phone_societe' => 'Numéro de téléphone de la société',
                'email_societe' => 'contact@societe.com',
                'total' => $request->montant,
                'montant_du' => $request->montant
            ]);

            // Charger les relations
            $transfert->load([
                'deviseSource',
                'deviseCible',
                'tauxEchange',
                'facture'
            ]);
            
            Mail::to($transfert->expediteur_email)->send(new TransfertNotification($transfert));

            $transfert->makeHidden('code'); 

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur créé avec succès. Veuillez vérifier votre email.',
                'data' => $transfert
            ], 201);
            // return $this->responseJson(true, 'Transfert effectué avec succès.', $transfert, 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'utilisateur.',
                'error' => $e->getMessage()
            ], 500);
            // return $this->responseJson(false, 'Une erreur est survenue lors de la création du transfert.', $e->getMessage(), 500);
        }
    }


}
