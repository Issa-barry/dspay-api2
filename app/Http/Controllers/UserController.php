<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // Afficher tous les utilisateurs
    public function index()
    {
        $users = User::all();  
        return response()->json($users);  
    }


    public function show($id) 
    {
        $user = User::find($id); 

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return response()->json($user); 
    }

    public function createContact(Request $request)
    {
        // $validated = $request->validate([
        //     'civilite' => 'required|in:Mr,Mme,Mlle,Autre',  
        //     'nom' => 'required|string|max:255',           
        //     'prenom' => 'required|string|max:255',           
        //     'email' => 'required|email|unique:users,email',  
        //     'phone' => 'required|string|unique:users,phone', 
        //     'date_naissance' => 'nullable|date',             
        //     'password' => 'required|string|min:8|confirmed', 
        // ]); 

        // $user = User::create([
        //     'civilite' => $validated['civilite'],       
        //     'nom' => $validated['nom'],                  
        //     'prenom' => $validated['prenom'],           
        //     'email' => $validated['email'],             
        //     'phone' => $validated['phone'],             
        //     'date_naissance' => $validated['date_naissance'], 
        //     'password' => Hash::make($validated['password']), 
        // ]);

        // return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $user], 201);
    }
 

    public function store(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'civilite' => 'required|in:Mr,Mme,Mlle,Autre',  
            'nom' => 'required|string|max:255',             
            'prenom' => 'required|string|max:255',        
            'phone' => 'required|string|unique:users,phone', 
            'date_naissance' => 'nullable|date',             
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',  
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $user = User::create([
                'civilite' => $request->civilite,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'phone' => $request->phone,
                'date_naissance' => $request->date_naissance,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role); // Assurez-vous que ce rôle existe

            // event(new Registered($user));
            $user->sendEmailVerificationNotification();

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
        return "hello";
    }


 
    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'civilite' => 'nullable|in:Mr,Mme,Mlle,Autre', 
            'nom' => 'sometimes|required|string|max:255',    
            'prenom' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($id)], 
            'phone' => ['sometimes', 'required', 'string', Rule::unique('users')->ignore($id)], 
            'date_naissance' => 'nullable|date',           
            'password' => 'nullable|string|min:8|confirmed', 
        ]);

        $user = User::find($id);  

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);  
        }
      
        $user->update($validated);

        return response()->json(['message' => 'Utilisateur mis à jour avec succès', 'user' => $user]);
    }


    public function destroy($id)
    {
        $user = User::find($id); 

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404); 
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès']);
    }
}
