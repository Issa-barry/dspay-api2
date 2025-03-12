<?php

namespace App\Http\Controllers\Taux;

use App\Http\Controllers\Controller;
use App\Models\Devise;
use App\Models\TauxEchange;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TauxCreateController extends Controller
{
    use JsonResponseTrait;

    /**
     * Créer un nouveau taux de change.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validation des données d'entrée
            $validated = $request->validate([
                'devise_source_id' => 'required|integer|exists:devises,id',
                'devise_cible_id' => 'required|integer|exists:devises,id',
                'taux' => 'required|numeric|min:0',
            ]);

            // Vérifier que les deux devises sont différentes
            if ($validated['devise_source_id'] === $validated['devise_cible_id']) {
                return $this->responseJson(false, 'Les devises source et cible doivent être différentes.', null, 400);
            }

            // Vérifier si le taux de change existe déjà
            $existingTaux = TauxEchange::where([
                ['devise_source_id', $validated['devise_source_id']],
                ['devise_cible_id', $validated['devise_cible_id']]
            ])->first();

            if ($existingTaux) {
                return $this->responseJson(false, 'Un taux de change entre ces deux devises existe déjà.', null, 409);
            }

            // Création du taux de change
            $tauxEchange = TauxEchange::create($validated);

            return $this->responseJson(true, 'Taux de change créé avec succès.', $tauxEchange, 201);
        } catch (ValidationException $e) {
            return $this->responseJson(false, 'Erreur de validation.', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la création du taux de change.', $e->getMessage(), 500);
        }
    }

     /**
     * Créer un nouveau taux de change avec noms des devises.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeByName(Request $request)
    {
        try {
            // Validation des données d'entrée
            $validated = $request->validate([
                'devise_source' => 'required|string|exists:devises,nom',
                'devise_cible' => 'required|string|exists:devises,nom',
                'taux' => 'required|numeric|min:0',
            ]);

            // Récupération des ID des devises par leur nom
            $deviseSource = Devise::where('nom', $validated['devise_source'])->first();
            $deviseCible = Devise::where('nom', $validated['devise_cible'])->first();

            if (!$deviseSource || !$deviseCible) {
                return $this->responseJson(false, 'Une ou plusieurs devises ne sont pas valides.', null, 400);
            }

            // Vérifier que les deux devises sont différentes
            if ($deviseSource->id === $deviseCible->id) {
                return $this->responseJson(false, 'Les devises source et cible doivent être différentes.', null, 400);
            }

            // Vérifier si le taux de change existe déjà
            $existingTaux = TauxEchange::where([
                ['devise_source_id', $deviseSource->id],
                ['devise_cible_id', $deviseCible->id]
            ])->first();

            if ($existingTaux) {
                return $this->responseJson(false, 'Un taux de change entre ces deux devises existe déjà.', null, 409);
            }

            // Création du taux de change
            $tauxEchange = TauxEchange::create([
                'devise_source_id' => $deviseSource->id,
                'devise_cible_id' => $deviseCible->id,
                'taux' => $validated['taux'],
            ]);

            return $this->responseJson(true, 'Taux de change créé avec succès.', [
                'id' => $tauxEchange->id,
                'devise_source_id' => $tauxEchange->devise_source_id,
                'devise_source_nom' => $deviseSource->nom,
                'devise_source_tag' => $deviseSource->tag,
                'devise_cible_id' => $tauxEchange->devise_cible_id,
                'devise_cible_nom' => $deviseCible->nom,
                'devise_cible_tag' => $deviseCible->tag,
                'taux' => $tauxEchange->taux,
                'created_at' => $tauxEchange->created_at,
                'updated_at' => $tauxEchange->updated_at,
            ], 201);
        } catch (ValidationException $e) {
            return $this->responseJson(false, 'Erreur de validation.', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la création du taux de change.', $e->getMessage(), 500);
        }
 }

}