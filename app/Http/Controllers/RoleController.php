<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
 
use Exception;


class RoleController extends Controller
{
    // Créer un rôle
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        return response()->json([
            'message' => 'Rôle créé avec succès.',
            'role' => $role,
        ], 201);
    }

    // Liste des rôles
    public function index()
    {
        $roles = Role::all();

        return response()->json(['roles' => $roles], 200);
    }

    // Afficher un rôle spécifique
    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Rôle introuvable.'], 404);
        }

        return response()->json(['role' => $role], 200);
    }

    // Mettre à jour un rôle
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
        ]);

        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Rôle introuvable.'], 404);
        }

        $role->name = $request->name;
        $role->save();

        return response()->json([
            'message' => 'Rôle mis à jour avec succès.',
            'role' => $role,
        ], 200);
    }

    // Supprimer un rôle
    public function destroy($id)
    {
        try {
            // Récupérer le rôle par ID
            $role = Role::findOrFail($id); 
            
            // Dissocier les utilisateurs du rôle, si nécessaire
            $role->users()->detach(); // Si le rôle est associé à des utilisateurs via une relation
            
            // Supprimer le rôle
            $role->delete();
    
            return response()->json(['message' => 'Rôle supprimé avec succès.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Rôle introuvable.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la suppression.'], 500);
        }
    }
    
 
}
