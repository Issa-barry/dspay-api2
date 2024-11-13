<?php
namespace App\Http\Controllers;

use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class AgenceController extends Controller
{
    // Liste toutes les agences
    public function index(): JsonResponse
    {
        $agences = Agence::all();
        return response()->json([
            'success' => true,
            'data' => $agences,
        ], 200);
    }

    // Affiche une agence spécifique
    public function show($id): JsonResponse
    {
        $agence = Agence::find($id);

        if (!$agence) {
            return response()->json([
                'success' => false,
                'message' => 'Agence non trouvée',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $agence,
        ], 200);
    }

    // Crée une nouvelle agence
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_creation' => 'required|date',
            'nom_agence' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:agences,phone',
            'email' => 'required|email|max:255|unique:agences,email',
            'statut' => 'required|in:active,attente,bloque,archive',
            
        ]);


        $agence = Agence::create($validated);

        return response()->json([
            'success' => true,
            'data' => $agence,
            'message' => 'Agence créée avec succès',
        ], 201);
    }

    // Met à jour une agence
    public function update(Request $request, $id): JsonResponse
    {
        $agence = Agence::find($id);

        if (!$agence) {
            return response()->json([
                'success' => false,
                'message' => 'Agence non trouvée',
            ], 404);
        }

        $validated = $request->validate([
            'nom_agence' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20|unique:agences,phone,' . $agence->id,
            'email' => 'sometimes|required|email|max:255|unique:agences,email,' . $agence->id,
            'statut' => 'sometimes|required|in:active,attente,bloque,archive',
        ]);

        $agence->update($validated);

        return response()->json([
            'success' => true,
            'data' => $agence,
            'message' => 'Agence mise à jour avec succès',
        ], 200);
    }

    // Supprime une agence
    public function destroy($id): JsonResponse
    {
        $agence = Agence::find($id);

        if (!$agence) {
            return response()->json([
                'success' => false,
                'message' => 'Agence non trouvée',
            ], 404);
        }

        $agence->delete();

        return response()->json([
            'success' => true,
            'message' => 'Agence supprimée avec succès',
        ], 200);
    }
}
