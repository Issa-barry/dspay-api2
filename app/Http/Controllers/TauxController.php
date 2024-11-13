<?php
namespace App\Http\Controllers;

use App\Models\Taux;
use Illuminate\Http\Request;

class TauxController extends Controller
{
    // Liste tous les taux
    public function index()
    {
        // return Taux::all();
        return response()->json(Taux::all(), 200);
    }

    // Affiche un taux spécifique
    public function show($id)
    {
        return Taux::findOrFail($id);
    }

    // Crée un nouveau taux
    public function store(Request $request)
    {
        $validated = $request->validate([
            'montant_fixe' => 'required|numeric',
            'pourcentage' => 'required|numeric',
        ]);

        return Taux::create($validated);
    }

    // Met à jour un taux
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'montant_fixe' => 'required|numeric',
            'pourcentage' => 'required|numeric',
        ]);

        $taux = Taux::findOrFail($id);
        $taux->update($validated);

        return $taux;
    }

    // Supprime un taux
    public function destroy($id)
    {
        $taux = Taux::findOrFail($id);
        $taux->delete();

        return response()->json(['message' => 'Taux supprimé avec succès']);
    }
}
