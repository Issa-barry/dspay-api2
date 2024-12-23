<?php
namespace App\Http\Controllers;

use App\Models\TauxEchange;
use App\Models\Devise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class TauxEchangeController extends Controller
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
     * Afficher tous les taux de change.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $tauxEchanges = TauxEchange::with(['deviseSource', 'deviseCible'])->get();
            return $this->responseJson(true, 'Liste des taux de change récupérée avec succès.', $tauxEchanges);
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la récupération des taux de change.', $e->getMessage(), 500);
        }
    }

    /**
     * Afficher un taux de change spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $tauxEchange = TauxEchange::with(['deviseSource', 'deviseCible'])->find($id);

            if (!$tauxEchange) {
                return $this->responseJson(false, 'Taux de change non trouvé.', null, 404);
            }

            return $this->responseJson(true, 'Taux de change récupéré avec succès.', $tauxEchange);
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la récupération du taux de change.', $e->getMessage(), 500);
        }
    }

    /**
     * Créer un nouveau taux de change.
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
            'taux' => 'required|numeric|min:0',
        ]);

        if ($validated->fails()) {
            return $this->responseJson(false, 'Validation échouée.', $validated->errors(), 422);
        }

        try {
            // Vérifier si le taux de change existe déjà entre les deux devises
            $existingTaux = TauxEchange::where('devise_source_id', $request->devise_source_id)
                ->where('devise_cible_id', $request->devise_cible_id)
                ->first();

            if ($existingTaux) {
                return $this->responseJson(false, 'Le taux de change existe déjà entre ces deux devises.', null, 409);
            }

            // Créer le taux de change
            $tauxEchange = TauxEchange::create([
                'devise_source_id' => $request->devise_source_id,
                'devise_cible_id' => $request->devise_cible_id,
                'taux' => $request->taux,
            ]);

            return $this->responseJson(true, 'Taux de change créé avec succès.', $tauxEchange, 201);
        } catch (Exception $e) {
            // Gestion des erreurs
            return $this->responseJson(false, 'Une erreur est survenue lors de la création du taux de change.', $e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour un taux de change existant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        // Validation des données d'entrée
        $validated = Validator::make($request->all(), [
            'devise_source_id' => 'required|exists:devises,id',
            'devise_cible_id' => 'required|exists:devises,id',
            'taux' => 'required|numeric|min:0',
        ]);

        if ($validated->fails()) {
            return $this->responseJson(false, 'Validation échouée.', $validated->errors(), 422);
        }

        try {
            // Trouver le taux de change
            $tauxEchange = TauxEchange::find($id);

            if (!$tauxEchange) {
                return $this->responseJson(false, 'Taux de change non trouvé.', null, 404);
            }

            // Vérifier si le taux de change existe déjà pour ces devises
            $existingTaux = TauxEchange::where('devise_source_id', $request->devise_source_id)
                ->where('devise_cible_id', $request->devise_cible_id)
                ->where('id', '!=', $id)
                ->first();

            if ($existingTaux) {
                return $this->responseJson(false, 'Le taux de change existe déjà entre ces deux devises.', null, 409);
            }

            // Mise à jour du taux de change
            $tauxEchange->update([
                'devise_source_id' => $request->devise_source_id,
                'devise_cible_id' => $request->devise_cible_id,
                'taux' => $request->taux,
            ]);

            return $this->responseJson(true, 'Taux de change mis à jour avec succès.', $tauxEchange);
        } catch (Exception $e) {
            // Gestion des erreurs
            return $this->responseJson(false, 'Une erreur est survenue lors de la mise à jour du taux de change.', $e->getMessage(), 500);
        }
    }

    /**
     * Supprimer un taux de change.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $tauxEchange = TauxEchange::find($id);

            if (!$tauxEchange) {
                return $this->responseJson(false, 'Taux de change non trouvé.', null, 404);
            }

            $tauxEchange->delete();

            return $this->responseJson(true, 'Taux de change supprimé avec succès.');
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la suppression du taux de change.', $e->getMessage(), 500);
        }
    }
}
