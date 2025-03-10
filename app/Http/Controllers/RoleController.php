<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Fonction pour centraliser les réponses JSON
     */
    protected function responseJson($success, $message, $data = null, $statusCode = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    } 
 

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|exists:roles,name',
        ]);
    
        // Récupérer l'utilisateur
        $user = User::find($request->user_id);
    
        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }
    
        // Assigner le rôle (utilisation de Spatie Permissions)
        try {
            $user->assignRole($request->role);
    
            return response()->json([
                'message' => 'Rôle assigné avec succès à l\'utilisateur.',
                'user' => $user,
                'role' => $request->role,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'assignation du rôle.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

 
    // /**
    //  * Assigner un rôle à un utilisateur.
    //  *
    //  * @param Request $request
    //  * @param int $userId
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function assignRole(Request $request, $userId)
    // {
    //     $request->validate([
    //         'role' => 'required|exists:roles,name',
    //     ]);

    //     $user = User::findOrFail($userId);
    //     $user->assignRole($request->role);

    //     return response()->json([
    //         'message' => "Le rôle {$request->role} a été assigné à l'utilisateur."
    //     ]);
    // http://127.0.0.1:8000/api/users/1/assign-role
    // }

    // /**
    //  * Retirer un rôle d'un utilisateur.
    //  *
    //  * @param Request $request
    //  * @param int $userId
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function revokeRole(Request $request, $userId)
    // {
    //     $request->validate([
    //         'role' => 'required|exists:roles,name',
    //     ]);

    //     $user = User::findOrFail($userId);
    //     $user->removeRole($request->role);

    //     return response()->json([
    //         'message' => "Le rôle {$request->role} a été retiré de l'utilisateur."
    //     ]);
    // }

    public function checkRoleUsers($id)
        {
            $role = Role::findOrFail($id);

            if ($role->hasUsers()) {
                return response()->json(['success' => false, 'message' => 'Ce rôle est assigné à des utilisateurs.'], 400);
            }

            return response()->json(['success' => true, 'message' => 'Ce rôle n\'est pas assigné à des utilisateurs.']);
        }


}
