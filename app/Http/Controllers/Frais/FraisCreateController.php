<?php

namespace App\Http\Controllers\Frais;

use App\Http\Controllers\Controller;
use App\Models\Frais;
use Illuminate\Http\Request;
use App\Traits\JsonResponseTrait;
use Illuminate\Validation\ValidationException;

class FraisCreateController extends Controller
{
    use JsonResponseTrait;
 
    /**
     * Ajouter un nouveau frais.
     */
    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'type' => 'required|in:fixe,pourcentage',
                'valeur' => 'required|numeric|min:0',
            ]);

            $frais = Frais::create($validated);
            return $this->responseJson(true, 'Frais créé avec succès.', $frais, 201);
        } catch (ValidationException $e) {
            return $this->responseJson(false, 'Erreur de validation.', $e->errors(), 422);
        }
    }

     
}
