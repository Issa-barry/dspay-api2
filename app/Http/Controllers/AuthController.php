<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    // Inscription
    public function register(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'civilite' => 'required|in:Mr,Mme,Mlle,Autre',  // Validation de la civilité
            'nom' => 'required|string|max:255',              // Validation du nom
            'prenom' => 'required|string|max:255',           // Validation du prénom
            'phone' => 'required|string|unique:users,phone', // Téléphone unique
            'date_naissance' => 'nullable|date',             // Date de naissance (facultatif)
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',  
        ]);

        // Si la validation échoue, retourner une erreur
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Création de l'utilisateur
            $user = User::create([
                'civilite' => $request->civilite,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_naissance' => $request->date_naissance,
                'password' => Hash::make($request->password),
            ]);

            // Assigner un rôle par défaut, par exemple "user"
            $user->assignRole($request->role); // Assurez-vous que ce rôle existe

            // event(new Registered($user));

            // Envoi de la notification de vérification de l'email
            $user->sendEmailVerificationNotification();

            // Création du token API
            $token = $user->createToken('Personal Access Token')->plainTextToken;

            return response()->json([
                'message' => 'Utilisateur créé avec succès. Veuillez vérifier votre email pour valider votre compte.',
                'user' => $user,
                'token' => $token,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de l\'enregistrement.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Connexion
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Les informations de connexion sont incorrectes.'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Votre email n\'a pas été vérifié. Vérifiez votre email et essayez à nouveau.',
                'email' => $user->email
            ], 400);
        }

        $token = $user->createToken('Personal Access Token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
        {
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Déconnecté de tous les appareils.',
            ], 200);
        }
    
    // Vérification de l'email
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (hash_equals($hash, sha1($user->getEmailForVerification()))) {
            $user->markEmailAsVerified();

            // Déclenche l'événement de vérification
            event(new Verified($user));

            return response()->json([
                'message' => 'Email vérifié avec succès.',
                'user' => $user
            ], 200);
        }

        return response()->json([
            'error' => 'Le lien de vérification est invalide.',
        ], 400);
    }

    // Réinitialisation du mot de passe
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérification de l'email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
        }

        // Mise à jour du mot de passe
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès.',
            'user' => $user,
        ], 200); 
    }

    public function sendResetPasswordLink(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $validator->errors()
                ], 400);
            }

            $status = Password::sendResetLink(
                $request->only('email')
            );

            return $status === Password::RESET_LINK_SENT
                ? response()->json(['message' => 'Lien de réinitialisation envoyé à votre email.'], 200)
                : response()->json(['error' => 'Une erreur est survenue lors de l\'envoi du lien.'], 500);
        }

      /**
     * Renvoie l'email de validation à un utilisateur.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendVerificationEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $validator->errors()
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'This email is already verified.'
            ], 200);
        }

        // Déclencher l'événement Registered pour renvoyer l'email
        event(new Registered($user));

        return response()->json([
            'message' => 'Verification email resent successfully.'
        ], 200);
    }
}
