<?php

namespace App\Http\Controllers;

use App\Models\Transfert;
use App\Models\TauxEchange;
use App\Models\Devise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class TransfertController extends Controller
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
     * Créer un transfert
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation des données d'entrée
        $validated = Validator::make($request->all(), [
            'devise_source_id' => 'required|exists:devises,id',
            'devise_cible_id' => 'required|exists:devises,id',
            'montant' => 'required|numeric|min:0',
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
            // Vérifier si le taux d'échange existe pour ces devises
            $tauxEchange = TauxEchange::where('devise_source_id', $request->devise_source_id)
                                      ->where('devise_cible_id', $request->devise_cible_id)
                                      ->first();

            if (!$tauxEchange) {
                return $this->responseJson(false, 'Taux de change introuvable pour ces devises.', null, 404);
            }

            // Calculer le montant converti
            $montantConverti = $request->montant * $tauxEchange->taux;

            // Créer le transfert
            $transfert = Transfert::create([
                'devise_source_id' => $request->devise_source_id,
                'devise_cible_id' => $request->devise_cible_id,
                'taux_echange_id' => $tauxEchange->id, // Lier le taux d'échange utilisé
                'montant' => $request->montant,
                'montant_converti' => $montantConverti,
                'receveur_nom' => $request->receveur_nom,
                'receveur_prenom' => $request->receveur_prenom,
                'receveur_phone' => $request->receveur_phone,
                'expediteur_nom' => $request->expediteur_nom,
                'expediteur_prenom' => $request->expediteur_prenom,
                'expediteur_phone' => $request->expediteur_phone,
                'expediteur_email' => $request->expediteur_email,
                'quartier' => $request->quartier,
            ]);

            return $this->responseJson(true, 'Transfert effectué avec succès.', $transfert, 201);
        } catch (Exception $e) {
            return $this->responseJson(false, 'Une erreur est survenue lors de la création du transfert.', $e->getMessage(), 500);
        }
    }

    /**
     * Afficher tous les transferts
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $transferts = Transfert::with(['deviseSource', 'deviseCible', 'tauxEchange'])->get();
            return $this->responseJson(true, 'Liste des transferts récupérée avec succès.', $transferts);
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la récupération des transferts.', $e->getMessage(), 500);
        }
    }

    /**
     * Afficher un transfert spécifique
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $transfert = Transfert::with(['deviseSource', 'deviseCible', 'tauxEchange'])->find($id);

            if (!$transfert) {
                return $this->responseJson(false, 'Transfert non trouvé.', null, 404);
            }

            return $this->responseJson(true, 'Transfert récupéré avec succès.', $transfert);
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la récupération du transfert.', $e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour un transfert
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validation des données d'entrée
        $validated = Validator::make($request->all(), [
            'devise_source_id' => 'required|exists:devises,id',
            'devise_cible_id' => 'required|exists:devises,id',
            'montant' => 'required|numeric|min:0',
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
            $transfert = Transfert::findOrFail($id);

            // Vérifier si le taux d'échange existe pour ces devises
            $tauxEchange = TauxEchange::where('devise_source_id', $request->devise_source_id)
                                      ->where('devise_cible_id', $request->devise_cible_id)
                                      ->first();

            if (!$tauxEchange) {
                return $this->responseJson(false, 'Taux de change introuvable pour ces devises.', null, 404);
            }

            // Mettre à jour le transfert
            $transfert->update([
                'devise_source_id' => $request->devise_source_id,
                'devise_cible_id' => $request->devise_cible_id,
                'taux_echange_id' => $tauxEchange->id, // Lier le taux d'échange utilisé
                'montant' => $request->montant,
                'montant_converti' => $request->montant * $tauxEchange->taux,
                'receveur_nom' => $request->receveur_nom,
                'receveur_prenom' => $request->receveur_prenom,
                'receveur_phone' => $request->receveur_phone,
                'expediteur_nom' => $request->expediteur_nom,
                'expediteur_prenom' => $request->expediteur_prenom,
                'expediteur_phone' => $request->expediteur_phone,
                'expediteur_email' => $request->expediteur_email,
                'quartier' => $request->quartier,
            ]);

            return $this->responseJson(true, 'Transfert mis à jour avec succès.', $transfert);
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la mise à jour du transfert.', $e->getMessage(), 500);
        }
    }

    /**
     * Supprimer un transfert
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $transfert = Transfert::findOrFail($id);
            $transfert->delete();
            return $this->responseJson(true, 'Transfert supprimé avec succès.');
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la suppression du transfert.', $e->getMessage(), 500);
        }
    }
}
