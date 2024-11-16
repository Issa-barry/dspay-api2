<?php
namespace App\Http\Controllers;

use App\Models\Adresse;
use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AgenceController extends Controller
{
    public function index() {
        $agences = Agence::with('adresse')->get();
        return response()->json([
            'success' => true,
            'message' => 'Liste des agences récupérée avec succès.',
            'data' => $agences
        ]);
    }

    public function store(Request $request) {
        try {
            $validated = $request->validate([
                'reference' => 'required|string|size:6|unique:agences,reference',
                'nom_agence' => 'required|string|max:255',
                'phone' => 'required|string|max:20|unique:agences,phone',
                'email' => 'required|email|max:255|unique:agences,email',
                'statut' => 'required|in:active,attente,bloque,archive',
                'date_creation' => 'required|date',
                'adresse' => 'required|array',
                'adresse.pays' => 'required|string|max:255',
                'adresse.adresse' => 'required|string|max:255',
                'adresse.complement_adresse' => 'nullable|string|max:255',
                'adresse.ville' => 'required|string|max:255',
                'adresse.code_postal' => 'required|string|max:20',
            ]);

            // Créer l'adresse
            $adresse = Adresse::create($validated['adresse']);

            // Créer l'agence
            $agence = Agence::create(array_merge($validated, ['adresse_id' => $adresse->id]));

            return response()->json([
                'success' => true,
                'message' => 'Agence créée avec succès.',
                'data' => $agence->load('adresse')
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de la validation des données.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show($id) {
        $agence = Agence::with('adresse')->find($id);
        if (!$agence) {
            return response()->json([
                'success' => false,
                'message' => 'Agence non trouvée.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Détails de l\'agence récupérés avec succès.',
            'data' => $agence
        ]);
    }

    public function update(Request $request, $id) {
        $agence = Agence::find($id);

        if (!$agence) {
            return response()->json([
                'success' => false,
                'message' => 'Agence non trouvée.'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'reference' => 'string|size:6|unique:agences,reference,' . $id,
                'nom_agence' => 'string|max:255',
                'phone' => 'string|max:20|unique:agences,phone,' . $id,
                'email' => 'email|max:255|unique:agences,email,' . $id,
                'statut' => 'in:active,attente,bloque,archive',
                'date_creation' => 'date',
                'adresse' => 'array',
                'adresse.pays' => 'string|max:255',
                'adresse.adresse' => 'string|max:255',
                'adresse.complement_adresse' => 'nullable|string|max:255',
                'adresse.ville' => 'string|max:255',
                'adresse.code_postal' => 'string|max:20',
            ]);

            if (isset($validated['adresse'])) {
                $agence->adresse->update($validated['adresse']);
            }

            $agence->update(collect($validated)->except('adresse')->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Agence mise à jour avec succès.',
                'data' => $agence->load('adresse')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de la validation des données.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy($id) {
        $agence = Agence::find($id);

        if (!$agence) {
            return response()->json([
                'success' => false,
                'message' => 'Agence non trouvée.'
            ], 404);
        }

        $agence->delete();

        return response()->json([
            'success' => true,
            'message' => 'Agence supprimée avec succès.'
        ], 204);
    }
}
