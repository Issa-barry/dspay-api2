<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionController extends Controller
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
     * Assigner un rôle à un utilisateur.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($userId);
        $user->assignRole($request->role);

        return response()->json([
            'message' => "Le rôle {$request->role} a été assigné à l'utilisateur."
        ]);
    }

    /**
     * Retirer un rôle d'un utilisateur.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($userId);
        $user->removeRole($request->role);

        return response()->json([
            'message' => "Le rôle {$request->role} a été retiré de l'utilisateur."
        ]);
    }

    /**
     * Assigner une permission à un utilisateur.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPermission(Request $request, $userId)
    {
        $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);

        $user = User::findOrFail($userId);
        $user->givePermissionTo($request->permission);

        return response()->json([
            'message' => "La permission {$request->permission} a été assignée à l'utilisateur."
        ]);
    }

    /**
     * Retirer une permission d'un utilisateur.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokePermission(Request $request, $userId)
    {
        $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);

        $user = User::findOrFail($userId);
        $user->revokePermissionTo($request->permission);

        return response()->json([
            'message' => "La permission {$request->permission} a été retirée de l'utilisateur."
        ]);
    }


     /**
     * Assigner une ou plusieurs permissions à un rôle.
     *
     * @param Request $request
     * @param int $roleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPermissionsToRole(Request $request, $roleId)
    {
        // Valider les permissions envoyées
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',  // chaque permission doit exister dans la table permissions
        ]);

        // Trouver le rôle par ID
        $role = Role::findOrFail($roleId);

        // Assigner les permissions au rôle
        $role->givePermissionTo($request->permissions);

        return response()->json([
            'message' => "Les permissions ont été assignées au rôle {$role->name}.",
        ]);
    }

     /**
     * Retirer une permission d'un rôle.
     *
     * @param Request $request
     * @param int $roleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokePermissionFromRole(Request $request, $roleId)
    {
        // Valider les permissions envoyées
        $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);

        // Trouver le rôle par ID
        $role = Role::findOrFail($roleId);

        // Retirer la permission du rôle
        $role->revokePermissionTo($request->permission);

        return response()->json([
            'message' => "La permission {$request->permission} a été retirée du rôle {$role->name}.",
        ]);
    }

     // Lister tous les rôles et permissions
     public function listRolesPermissions()
     {
         $roles = Role::with('permissions')->get();
        //  $permissions = Permission::all();
         $permissions = Permission::all()->groupBy('model_type');
     
         return response()->json([
             'success' => true,
             'message' => 'Liste des rôles et permissions récupérée avec succès.',
             'data' => [
                 'roles' => $roles,
                //  'permissions' => $permissions,
             ]
         ], 200);
     }

     public function getRolePermissions($roleId)
        {
            // Trouver le rôle avec ses permissions associées
            $role = Role::with('permissions')->findOrFail($roleId);

            // Organiser les permissions par modèle (si vous souhaitez une structure similaire à celle de la réponse précédente)
            $permissions = $role->permissions->groupBy('model_type');

            return response()->json([
                'success' => true,
                'message' => "Les permissions du rôle {$role->name} ont été récupérées avec succès.",
                'data' => [
                    'role' => $role,               // Renvoyer les informations du rôle
                    // 'permissions' => $permissions  // Renvoyer les permissions groupées par 'model_type'
                ]
            ], 200);
        }
     
}
