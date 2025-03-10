<?php

namespace App\Http\Controllers\Taux;

use App\Http\Controllers\Controller;
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
}
