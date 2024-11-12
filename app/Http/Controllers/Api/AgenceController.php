<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Adresse;
use Illuminate\Http\Request;

class AgenceController extends Controller
{
    public function store()
    {
        // $validated = $request->validate([
        //     'nom' => 'required|string|max:255',
        //     'phone' => 'required|string|max:20',
        //     'email' => 'required|email|unique:agences,email',
        //     'adresse' => 'required|array',
        //     'adresse.pays' => 'required|string|max:255',
        //     'adresse.adresse' => 'required|string|max:255',
        //     'adresse.complement_adresse' => 'nullable|string|max:255',
        //     'adresse.ville' => 'required|string|max:255',
        //     'adresse.code_postal' => 'required|string|max:20',
        //     'date_creation' => 'required|date',
        // ]);

        return ("gell");

        // Création de l'adresse
        // $adresse = Adresse::create($validated['adresse']);

        // Générer une référence unique pour l'agence
        // $reference = 'AG-' . strtoupper(uniqid());

        // // Création de l'agence
        // $agence = Agence::create([
        //     'reference' => $reference,
        //     'nom' => $validated['nom'],
        //     'phone' => $validated['phone'],
        //     'email' => $validated['email'],
        //     'adresse_id' => $adresse->id,
        //     'date_creation' => $validated['date_creation'],
        // ]);

        // return response()->json($agence->load('adresse'), 201);
    }

    public function update(Request $request, $id)
    {
        $agence = Agence::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|unique:agences,email,' . $agence->id,
            'adresse' => 'sometimes|array',
            'adresse.pays' => 'sometimes|required|string|max:255',
            'adresse.adresse' => 'sometimes|required|string|max:255',
            'adresse.complement_adresse' => 'nullable|string|max:255',
            'adresse.ville' => 'sometimes|required|string|max:255',
            'adresse.code_postal' => 'sometimes|required|string|max:20',
            'date_creation' => 'sometimes|required|date',
        ]);

        if (isset($validated['adresse'])) {
            $agence->adresse->update($validated['adresse']);
        }

        $agence->update($validated);

        return response()->json($agence->load('adresse'));
    }

    public function destroy($id)
    {
        $agence = Agence::findOrFail($id);
        $agence->delete();
        return response()->json(null, 204);
    }

    public function index()
    {
        $agences = Agence::with('adresse')->get();
        return response()->json($agences);
    }
}
