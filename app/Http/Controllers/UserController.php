<?php

namespace App\Http\Controllers;

use App\Models\Adresse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;

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

    // Créer un nouvel utilisateur
    public function store(Request $request)
    {
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

        // Créer l'adresse
        $adresse = Adresse::create($validated['adresse']);
        $role = Role::where('name', $validated['role'])->firstOrFail();

        try {
            $user = User::create([
                'civilite' => $validated['civilite'],
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'date_naissance' => $validated['date_naissance'],
                'password' => Hash::make($validated['password']),
                'adresse_id' => $adresse->id,
                'role_id' => $role->id, // Ajout de l'ID du rôle
            ]);

            $user->assignRole($validated['role']);
            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur créé avec succès. Veuillez vérifier votre email.',
                'data' => array_merge($user->load('adresse')->toArray(), ['role' => $user->role])
                // 'data' => $user->load('adresse')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'utilisateur.',
                'error' => $e->getMessage()
            ], 500);
        }

        
    }

    // Mettre à jour un utilisateur
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }

        $validated = $request->validate([
            'civilite' => 'nullable|in:Mr,Mme,Mlle,Autre',
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'phone' => 'sometimes|required|string|unique:users,phone,' . $id,
            'date_naissance' => 'nullable|date',
            'password' => 'nullable|string|min:8|confirmed',
            'adresse' => 'sometimes|array',
            'adresse.pays' => 'string|max:255',
            'adresse.adresse' => 'string|max:255',
            'adresse.complement_adresse' => 'nullable|string|max:255',
            'adresse.ville' => 'string|max:255',
            'adresse.code_postal' => 'string|max:20',
        ]);

        if (isset($validated['adresse'])) {
            $user->adresse->update($validated['adresse']);
        }

        $user->update(collect($validated)->except('adresse')->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès.',
            'data' => array_merge($user->load('adresse')->toArray(), ['role' => $user->role])
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