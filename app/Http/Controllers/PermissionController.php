<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
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
     * Crée une nouvelle permission.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    { 
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        try { 
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

    public function index()
    {
        $permissions = Permission::all();
         return response()->json([
            'success' => true,
            'message' => 'Liste des Devises récupérée avec succès.',
            'data' => $permissions
        ], 200);

     }
 
    public function show($id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(['error' => 'Permission introuvable.'], 404);
        }

        return response()->json(['permission' => $permission], 200);
    }
 
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
