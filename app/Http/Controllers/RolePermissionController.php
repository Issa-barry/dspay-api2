<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionController extends Controller
{
    // Assigner un rôle à un utilisateur
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::find($request->user_id);
        // $user->assignRole($request->role);
        die($user);

        return response()->json(['message' => 'Rôle assigné avec succès.'], 200);
    }

    // Retirer un rôle à un utilisateur
    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::find($request->user_id);
        $user->removeRole($request->role);

        return response()->json(['message' => 'Rôle retiré avec succès.'], 200);
    }

    // Vérifier si un utilisateur a un rôle
    public function checkUserRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string',
        ]);

        $user = User::find($request->user_id);

        return response()->json([
            'hasRole' => $user->hasRole($request->role),
        ], 200);
    }

    // Donner une permission à un utilisateur
    public function givePermission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|string|exists:permissions,name',
        ]);

        $user = User::find($request->user_id);
        $user->givePermissionTo($request->permission);

        return response()->json(['message' => 'Permission accordée avec succès.'], 200);
    }

    // Retirer une permission à un utilisateur
    public function revokePermission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|string|exists:permissions,name',
        ]);

        $user = User::find($request->user_id);
        $user->revokePermissionTo($request->permission);

        return response()->json(['message' => 'Permission retirée avec succès.'], 200);
    }

    // Assigner une permission à un rôle
    public function assignPermissionToRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
            'permission' => 'required|string|exists:permissions,name',
        ]);

        $role = Role::findByName($request->role);
        $role->givePermissionTo($request->permission);

        return response()->json(['message' => 'Permission assignée au rôle avec succès.'], 200);
    }

    // Retirer une permission d’un rôle
    public function removePermissionFromRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
            'permission' => 'required|string|exists:permissions,name',
        ]);

        $role = Role::findByName($request->role);
        $role->revokePermissionTo($request->permission);

        return response()->json(['message' => 'Permission retirée du rôle avec succès.'], 200);
    }
}
