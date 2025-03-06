<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class updateUserController extends Controller
{
    // Mettre à jour un utilisateur
    public function update(Request $request, $id)
    {
        
        try {
            $user = User::find($id);
    
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé.'
                ], 404);
            }
    
            //  Validation des données
            $validator = Validator::make($request->all(), [
                'civilite' => 'nullable|in:Mr,Mme,Mlle,Autre',
                'nom' => 'sometimes|required|string|max:255',
                'prenom' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'phone' => [
                    'sometimes',
                    'required',
                    'string',
                    Rule::unique('users', 'phone')->ignore($id), // Ignore le téléphone actuel
                ],
                'date_naissance' => 'nullable|date',
                 'role' => [
                    'sometimes',
                    'required',
                    Rule::exists('roles', 'name'), // Vérifie si le rôle existe
                ],
                'adresse' => 'sometimes|array',
                'adresse.pays' => 'string|max:255',
                'adresse.adresse' => 'string|max:255',
                'adresse.complement_adresse' => 'nullable|string|max:255',
                'adresse.ville' => 'string|max:255',
                'adresse.quartier' => 'string|max:255',
                'adresse.code_postal' => 'string|max:20',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation des données.',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            $validated = $validator->validated();
    
            // Mise à jour de l'adresse si fournie
            if (isset($validated['adresse']) && $user->adresse) {
                $user->adresse->update($validated['adresse']);
            }
    
            // Mise à jour du rôle et du `role_id`
            if (isset($validated['role'])) {
                $newRole = Role::where('name', $validated['role'])->first();
    
                if (!$newRole) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le rôle fourni n\'existe pas.'
                    ], 400);
                }
    
                //  Mise à jour de `role_id` dans `users`
                $user->role_id = $newRole->id;
                $user->save();
    
                //  Mise à jour des permissions (Spatie)
                $user->syncRoles([$validated['role']]);
            }
    
            //  Mise à jour des autres champs
            $user->update(collect($validated)->except(['adresse', 'role'])->toArray());
    
            return response()->json([
                'success' => true,
                'message' => 'Utilisateur mis à jour avec succès.',
                'data' => array_merge($user->load(['adresse', 'roles'])->toArray(), [
                    'role' => $user->roles->pluck('name')->first(), //  Retourne le rôle proprement
                    'role_id' => $user->role_id // Retourne aussi le `role_id`
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur interne est survenue lors de la mise à jour de l\'utilisateur.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}
