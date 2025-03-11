<?php

namespace App\Http\Controllers\Taux;

use App\Http\Controllers\Controller;
use App\Models\TauxEchange;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TauxUpdateController extends Controller
{
    use JsonResponseTrait;

    /**
     * Mettre à jour un taux de change existant.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateById(Request $request, $id)
    {
        try {
            // Validation des données d'entrée
            $validated = $request->validate([
                'devise_source_id' => 'required|integer|exists:devises,id',
                'devise_cible_id' => 'required|integer|exists:devises,id',
                'taux' => 'required|numeric|min:0',
            ]);

            // Vérifier que les devises ne sont pas les mêmes
            if ($validated['devise_source_id'] === $validated['devise_cible_id']) {
                return $this->responseJson(false, 'Les devises source et cible doivent être différentes.', null, 400);
            }

            // Trouver le taux de change existant
            $tauxEchange = TauxEchange::find($id);
            if (!$tauxEchange) {
                return $this->responseJson(false, 'Taux de change non trouvé.', null, 404);
            }

            // Vérifier si un taux de change similaire existe déjà
            $existingTaux = TauxEchange::where([
                ['devise_source_id', $validated['devise_source_id']],
                ['devise_cible_id', $validated['devise_cible_id']],
                ['id', '!=', $id]
            ])->first();

            if ($existingTaux) {
                return $this->responseJson(false, 'Un taux de change entre ces deux devises existe déjà.', null, 409);
            }

            // Mettre à jour le taux de change
            $tauxEchange->update($validated);

            return $this->responseJson(true, 'Taux de change mis à jour avec succès.', $tauxEchange);
        } catch (ValidationException $e) {
            return $this->responseJson(false, 'Erreur de validation.', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la mise à jour du taux de change.', $e->getMessage(), 500);
        }
    }
}
