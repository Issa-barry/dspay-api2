<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AgenceUpdateController extends Controller
{
    use JsonResponseTrait; 

    /**
     * Mettre à jour une agence.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateById(Request $request, $id)
    {
        try { 
            if (!is_numeric($id)) {
                return $this->responseJson(false, 'ID invalide.', null, 400);
            }
 
            $agence = Agence::find($id);
            if (!$agence) {
                return $this->responseJson(false, 'Agence non trouvée.', null, 404);
            }
 
            $validated = $request->validate([
                'reference' => 'string|min:5|max:6|unique:agences,reference,' . $id,
                'nom_agence' => 'string|max:255',
                'phone' => 'string|max:20|unique:agences,phone,' . $id,
                'email' => 'email|max:255|unique:agences,email,' . $id,
                'statut' => 'in:active,attente,bloque,archive',
                'date_creation' => 'date',
                'adresse' => 'array',
                'adresse.pays' => 'string|max:255',
                'adresse.adresse' => 'string|max:255',
                'adresse.complement_adresse' => 'nullable|string|max:255',
                'adresse.ville' => 'string|max:255',
                'adresse.code_postal' => 'string|max:20',
            ]);
 
            if (!empty($validated['adresse']) && $agence->adresse) {
                $agence->adresse->update($validated['adresse']);
            }
 
            $agence->update(collect($validated)->except('adresse')->toArray());

            return $this->responseJson(true, 'Agence mise à jour avec succès.', $agence->load('adresse'));
        } catch (ValidationException $e) {
            return $this->responseJson(false, 'Erreur de validation des données.', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->responseJson(false, 'Une erreur interne est survenue lors de la mise à jour.', $e->getMessage(), 500);
        }
    }
}
