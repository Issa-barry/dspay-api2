<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Agence;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;

class UserAffecterAgenceController extends Controller
{
    use JsonResponseTrait;

    /**
     * Affecter un utilisateur à une agence.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function affecterAgence(Request $request, $userId)
    {
        try {
            // Validation des données entrantes
            $validated = $request->validate([
                'agence_id' => 'required|exists:agences,id'
            ]);

            // Recherche de l'utilisateur
            $user = User::findOrFail($userId);

            // Vérifier si l'utilisateur est déjà affecté à cette agence
            if ($user->agence_id == $validated['agence_id']) {
                return $this->responseJson(false, 'L\'utilisateur est déjà affecté à cette agence.', null, 400);
            }

            // Mise à jour de l'affectation
            $user->update(['agence_id' => $validated['agence_id']]);

            return $this->responseJson(true, 'Utilisateur affecté à l\'agence avec succès.', $user->load('agence'), 200);
        } catch (ValidationException $e) {
            return $this->responseJson(false, 'Erreur de validation.', $e->errors(), 422);
        } catch (QueryException $e) {
            Log::error('Erreur SQL lors de l\'affectation de l\'utilisateur à une agence : ' . $e->getMessage());
            return $this->responseJson(false, 'Erreur de base de données.', null, 500);
        } catch (Exception $e) {
            Log::error('Erreur générale lors de l\'affectation : ' . $e->getMessage());
            return $this->responseJson(false, 'Une erreur inattendue est survenue.', null, 500);
        }
    }
}
