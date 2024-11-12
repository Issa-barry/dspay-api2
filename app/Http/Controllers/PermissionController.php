<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
     
     /**
     * Crée une nouvelle permission.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        // Validation de la requête
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        try {
            // Création de la permission avec le guard 'api'
            $permission = Permission::create([
                'name' => $validated['name'],
                'guard_name' => 'web',  // Utiliser le guard 'api'
            ]);

            return response()->json([
                'message' => 'Permission créée avec succès.',
                'permission' => $permission
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la permission.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Liste des permissions
    public function index()
    {
        $permissions = Permission::all();

        return response()->json(['permissions' => $permissions], 200);
    }

    // Afficher une permission spécifique
    public function show($id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(['error' => 'Permission introuvable.'], 404);
        }

        return response()->json(['permission' => $permission], 200);
    }

    // Mettre à jour une permission
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $id,
        ]);

        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(['error' => 'Permission introuvable.'], 404);
        }

        $permission->name = $request->name;
        $permission->save();

        return response()->json([
            'message' => 'Permission mise à jour avec succès.',
            'permission' => $permission,
        ], 200);
    }

    // Supprimer une permission
    public function destroy($id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(['error' => 'Permission introuvable.'], 404);
        }

        $permission->delete();

        return response()->json(['message' => 'Permission supprimée avec succès.'], 200);
    }
}
