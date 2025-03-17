<?php

namespace App\Http\Controllers\User;

use OpenApi\Annotations as OA;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\JsonResponseTrait; 
use Exception;
use Illuminate\Http\Request;

class ShowUserController extends Controller
{
    use JsonResponseTrait; 
        /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Liste des utilisateurs",
     *     description="Retourne la liste des utilisateurs",
     *     @OA\Response(
     *         response=200,
     *         description="Succès"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     * Récupérer la liste de tous les utilisateurs avec leurs adresses et rôles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    { 
        try {
            $users = User::with(['adresse', 'roles'])->get();

            return $this->responseJson(true, 'Liste des utilisateurs récupérée avec succès.', 
                $users->map(function ($user) {
                    return array_merge($user->toArray(), [
                        'role' => $user->roles->pluck('name') 
                    ]);
                })
            );
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la récupération des utilisateurs.', $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les détails d'un utilisateur spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getById($id)
    {
        try {
           
            if (!is_numeric($id)) {
                return $this->responseJson(false, 'ID utilisateur invalide.', null, 400);
            }

            $user = User::with(['adresse', 'roles'])->find($id);

            if (!$user) {
                return $this->responseJson(false, 'Utilisateur non trouvé.', null, 404);
            }

            return $this->responseJson(true, 'Détails de l\'utilisateur récupérés avec succès.', 
                array_merge($user->toArray(), [
                    'role' => $user->roles->pluck('name') 
                ])
            );
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la récupération de l\'utilisateur.', $e->getMessage(), 500);
        }
    }
}
