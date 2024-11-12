<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionController extends Controller
{ 
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
}
