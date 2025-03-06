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

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:roles,name',
            ]);

            $validated['name'] = ucfirst($validated['name']); 

            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',  
            ]);

            return $this->responseJson(true, 'Rôle créé avec succès.', $role, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->responseJson(false, 'Erreur de validation.', $e->errors(), 422);

        } catch (\Exception $e) {
            return $this->responseJson(false, 'Une erreur est survenue lors de la création du rôle.', $e->getMessage(), 500);
        }
    }
 
    public function index()
    {
        $roles = Role::all();
        return response()->json([
            'success' => true,
            'message' => 'Liste des Devises récupérée avec succès.',
            'data' => $roles,
        ]);
    }
 
    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Rôle introuvable.'], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Liste des Devises récupérée avec succès.',
            'data' => $role,
        ]);
    }
 
    public function findRoleByName(Request $request)
{
    $request->validate([
        'name' => 'required|string',
    ]);

    $role = Role::where('name', $request->name)->first();

    if (!$role) {
        return $this->responseJson(false, 'Rôle introuvable.', null, 404);
    }

    return $this->responseJson(true, 'Rôle trouvé avec succès.', $role);
}


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
        ]);

        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Rôle introuvable.'], 404);
        }

        $role->name = ucfirst($request->name);
        $role->save();

        return response()->json([
            'message' => 'Rôle mis à jour avec succès.',
            'role' => $role,
        ], 200);
    }

    //Avant conrole si affecter a utilisateur
    // public function destroy($id)
    // {
    //     try { 
    //         $role = Role::findOrFail($id);

    //         // Dissocier les utilisateurs ou permissions liés avant suppression
    //         $role->users()->detach(); // Si relation avec des utilisateurs
    //         $role->permissions()->detach(); // Si relation avec des permissions
 
    //         $role->delete();

    //         return $this->responseJson(true, 'Rôle supprimé avec succès.');
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         return $this->responseJson(false, 'Rôle introuvable.', null, 404);
    //     } catch (\Exception $e) {
    //         return $this->responseJson(false, 'Une erreur est survenue lors de la suppression du rôle.', $e->getMessage(), 500);
    //     }
    // }

    public function destroy($id)
        {
            try {
                // Trouver le rôle par ID
                $role = Role::findOrFail($id);

                // // Vérifier si le rôle est associé à des utilisateurs
                // if ($role->users()->exists()) {
                //     return $this->responseJson(false, 'Ce rôle est assigné à des utilisateurs donc ne peut pas être supprimé.', null, 400);
                // }

                // Dissocier les permissions liées avant suppression
                $role->permissions()->detach();

                // Supprimer le rôle
                $role->delete();

                return $this->responseJson(true, 'Rôle supprimé avec succès.');
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return $this->responseJson(false, 'Rôle introuvable.', null, 404);
            } catch (\Exception $e) {
                return $this->responseJson(false, 'Une erreur est survenue lors de la suppression du rôle.', $e->getMessage(), 500);
            }
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
