<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ShowUserController extends Controller
{
    public function index()
    { 
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

}
