<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Adresse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Liste des utilisateurs avec leur adresse
    public function index()
    {
        // $users = User::with('adresse')->get();
        $users = User::with(['adresse', 'roles'])->get();
        return response()->json([
            'success' => true,
            'message' => 'Liste des utilisateurs récupérée avec succès.',
            'data' => $users->map(function ($user) {
            return array_merge($user->toArray(), ['role' => $user->role]);
            })
            // 'data' => $users
        ]);
    } 

    // Afficher un utilisateur par ID
    public function show($id)
    {
        $user = User::with(['adresse', 'roles'])->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }
 
        return response()->json([
            'success' => true,
            'message' => 'Détails de l\'utilisateur récupérés avec succès.',
            'data' => $user
            // 'data' => array_merge($user->toArray(), ['role' => $user->role])
        ]);
    }


    // Supprimer un utilisateur
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès.'
        ]);
    }
}