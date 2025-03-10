<?php

namespace App\Http\Controllers\Agence;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Traits\JsonResponseTrait;
 
class AgenceShowController extends Controller
{
    use JsonResponseTrait;  

    /**
     * Récupérer la liste des agences avec leurs adresses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try { 
            $agences = Agence::with('adresse')->get();

            return $this->responseJson(true, 'Liste des agences récupérée avec succès.', $agences);
        } catch (\Exception $e) {
            return $this->responseJson(false, 'Une erreur interne est survenue.', $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les détails d'une agence spécifique.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
          
            if (!is_numeric($id)) {
                return $this->responseJson(false, 'ID invalide.', null, 400);
            }
 
            $agence = Agence::with('adresse')->find($id);

            if (!$agence) {
                return $this->responseJson(false, 'Agence non trouvée.', null, 404);
            }

            return $this->responseJson(true, 'Détails de l\'agence récupérés avec succès.', $agence);
        } catch (\Exception $e) {
            return $this->responseJson(false, 'Une erreur interne est survenue.', $e->getMessage(), 500);
        }
    }
}
