<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Adresse;
use App\Models\Role;
use App\Models\User;
use Exception;
use Hash;
use Illuminate\Http\Request;

class createUserController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([  
                'civilite' => 'in:Mr,Mme,Mlle,Autre',
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|unique:users,phone',
                'date_naissance' => 'nullable|date',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|string|exists:roles,name',
                'adresse' => 'required|array',
                'adresse.pays' => 'required|string|max:255',
                'adresse.adresse' => 'required|string|max:255',
                'adresse.complement_adresse' => 'nullable|string|max:255',
                'adresse.ville' => 'required|string|max:255',
                'adresse.quartier' => 'required|string|max:255',
                'adresse.code_postal' => 'required|string|max:20',
            ]);

            $adresse = Adresse::create($validated['adresse']);
            $role = Role::where('name', $validated['role'])->firstOrFail();

            $user = User::create([
                'civilite' => $validated['civilite'],
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'date_naissance' => $validated['date_naissance'],
                'password' => Hash::make($validated['password']),
                'adresse_id' => $adresse->id,
                'role_id' => $role->id,
            ]);

            $user->assignRole($validated['role']);
            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur créé avec succès. Veuillez vérifier votre email.',
                'data' => $user->load('adresse')
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'utilisateur.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
