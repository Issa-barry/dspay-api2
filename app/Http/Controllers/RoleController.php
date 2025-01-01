<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
 
use Exception;


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
            'data' => $data
        ], $statusCode);
    } 


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
        // $devise =  Devise::all();
        $roles = Role::all();
        return response()->json([
            'success' => true,
            'message' => 'Liste des Devises récupérée avec succès.',
            'data' => $roles
        ]);
    }

    // Afficher un rôle spécifique
    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Rôle introuvable.'], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Liste des Devises récupérée avec succès.',
            'data' => $role
        ]);
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

        $role->name = ucfirst($request->name);
        $role->save();

        return response()->json([
            'message' => 'Rôle mis à jour avec succès.',
            'role' => $role,
        ], 200);
    }

    // Supprimer un rôle
    // Supprimer un rôle
public function destroy($id)
{
    try {
        // Récupérer le rôle par ID
        $role = Role::findOrFail($id);

        // Dissocier les utilisateurs ou permissions liés avant suppression
        $role->users()->detach(); // Si relation avec des utilisateurs
        $role->permissions()->detach(); // Si relation avec des permissions

        // Supprimer le rôle
        $role->delete();

        return $this->responseJson(true, 'Rôle supprimé avec succès.');
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return $this->responseJson(false, 'Rôle introuvable.', null, 404);
    } catch (\Exception $e) {
        return $this->responseJson(false, 'Une erreur est survenue lors de la suppression du rôle.', $e->getMessage(), 500);
    }
}

    

     // Assigner un rôle à un utilisateur
    //  public function assignRole( )
    //  {
    //     //  $request->validate([
    //     //      'user_id' => 'required|exists:users,id',
    //     //      'role' => 'required|string|exists:roles,name',
    //     //  ]);
 
    //     //  $user = User::find($request->user_id);
    //     //  $user->assignRole($request->role);
  
    //     //  return response()->json(['message' => 'Rôle assigné avec succès.'], 200);
    //  }
 
 
       
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
             'role' => 'required|string|exists:roles,name',
        ]);

         $user = User::find($request->user_id);
        die($user->assignRole($request->role));
        // return response()->json([
        //     'message' => 'Rôle créé avec succès.',
        //     'role' => $role,
        // ], 201);
    }
}