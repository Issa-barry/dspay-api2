<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with('user.adresse')->get();

        return response()->json([
            'success' => true,
            'message' => 'Liste des agents récupérée avec succès.',
            'data' => $agents->map(function ($agent) {
                $user = $agent->user;
                return array_merge($agent->toArray(), [
                    'user' => array_merge($user->toArray(), ['role' => $user->role])
                ]);
            })
        ]);
    }

    public function show($id)
    {
        $agent = Agent::with('user.adresse')->find($id);

        if (!$agent) {
            return response()->json([
                'success' => false,
                'message' => 'Agent non trouvé.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Détails de l\'agent récupérés avec succès.',
            // 'data' => $agent
            'data' => array_merge($agent->toArray(), [
                'user' => array_merge($agent->user->load('adresse')->toArray(), ['role' => $agent->user->role])
            ])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'civilite' => 'required|in:Mr,Mme,Mlle,Autre',
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
            'adresse.code_postal' => 'required|string|max:20',
            'poste_occupe' => 'nullable|string|max:255',
        ]);

        $adresse = \App\Models\Adresse::create($validated['adresse']);

        $user = User::create([
            'civilite' => $validated['civilite'],
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'date_naissance' => $validated['date_naissance'],
            'password' => Hash::make($validated['password']),
            'adresse_id' => $adresse->id,
        ]);

        $agent = Agent::create([
            'user_id' => $user->id,
            'poste_occupe' => $validated['poste_occupe'],
        ]);

        // Assigner un rôle par défaut, par exemple "user"
        $user->assignRole($request->role); // Assurez-vous que ce rôle existe

        // Envoi de la notification de vérification de l'email
        $user->sendEmailVerificationNotification();

        // Création du token API
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Agent créé avec succès.',
            // 'data' => $agent->load('user.adresse')
            'data' => array_merge($agent->toArray(), [
                'user' => array_merge($agent->user->load('adresse')->toArray(), ['role' => $agent->user->role])
            ])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $agent = Agent::with('user')->find($id);

        if (!$agent) {
            return response()->json([
                'success' => false,
                'message' => 'Agent non trouvé.'
            ], 404);
        }

        $validated = $request->validate([
            'civilite' => 'nullable|in:Mr,Mme,Mlle,Autre',
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $agent->user->id,
            'phone' => 'sometimes|required|string|unique:users,phone,' . $agent->user->id,
            'date_naissance' => 'nullable|date',
            'password' => 'nullable|string|min:8|confirmed',
            'adresse' => 'sometimes|array',
            'adresse.pays' => 'string|max:255',
            'adresse.adresse' => 'string|max:255',
            'adresse.complement_adresse' => 'nullable|string|max:255',
            'adresse.ville' => 'string|max:255',
            'adresse.code_postal' => 'string|max:20',
            'poste_occupe' => 'nullable|string|max:255',
        ]);

        if (isset($validated['adresse'])) {
            $agent->user->adresse->update($validated['adresse']);
        }

        $agent->user->update(collect($validated)->except(['adresse', 'poste_occupe'])->toArray());
        $agent->update(['poste_occupe' => $validated['poste_occupe']]);

        return response()->json([
            'success' => true,
            'message' => 'Agent mis à jour avec succès.',
            // 'data' => $agent->load('user.adresse')
            'data' => array_merge($agent->toArray(), [
                'user' => array_merge($agent->user->load('adresse')->toArray(), ['role' => $agent->user->role])
            ])
        ]);
    }

    public function destroy($id)
    {
        $agent = Agent::find($id);

        if (!$agent) {
            return response()->json([
                'success' => false,
                'message' => 'Agent non trouvé.'
            ], 404);
        }

        // $agent->delete();

        return response()->json([
            'success' => true,
            'message' => "Veuilleiz utiliser le web-service user pour la supression d'un agent : http://127.0.0.1:8000/api/users/{id}."
        ]);
    }
}
