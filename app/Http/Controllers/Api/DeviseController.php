<?php
 namespace App\Http\Controllers\Api;

 use App\Http\Controllers\Controller;
 use App\Models\Devise;
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Auth;
 
 class DeviseController extends Controller
 {
     public function index()
     {
        //  return response()->json(Devise::all(), 200);

        if (!Auth::user()->can('create-devise')) {
            return response()->json(['message' => 'Vous n\'avez pas la permission de créer une devise.'], 403);
        }
     }
 

    public function store(Request $request)
        {
            try {
                $validated = $request->validate([
                    'nom' => 'required|string|max:255',
                    'tag' => 'required|string|max:10|unique:devises',
                ]);

                $devise = Devise::create($validated);

                return response()->json([
                    'message' => 'Devise créée avec succès.',
                    'data' => $devise
                ], 201);

            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'message' => 'Erreur de validation.',
                    'errors' => $e->errors()
                ], 422);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Une erreur est survenue lors de la création de la devise.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }


 
     public function show($id)
     {
         $devise = Devise::find($id);
         if (!$devise) {
             return response()->json(['message' => 'Devise not found'], 404);
         }
         return response()->json($devise, 200);
     }
 
     public function update(Request $request, $id)
     {
         try {
             $devise = Devise::find($id);
             if (!$devise) {
                 return response()->json(['message' => 'Devise non trouvée.'], 404);
             }
     
             $validated = $request->validate([
                 'nom' => 'sometimes|required|string|max:255',
                 'tag' => 'sometimes|required|string|max:10|unique:devises,tag,' . $id,
             ]);
     
             $devise->update($validated);
     
             return response()->json([
                 'message' => 'Devise mise à jour avec succès.',
                 'data' => $devise
             ], 200);
     
         } catch (\Illuminate\Validation\ValidationException $e) {
             return response()->json([
                 'message' => 'Erreur de validation.',
                 'errors' => $e->errors()
             ], 422);
     
         } catch (\Exception $e) {
             return response()->json([
                 'message' => 'Une erreur est survenue lors de la mise à jour de la devise.',
                 'error' => $e->getMessage()
             ], 500);
         }
     }
     
 
     public function destroy($id)
     {
         $devise = Devise::find($id);
         if (!$devise) {
             return response()->json(['message' => 'Devise not found'], 404);
         }
 
         $devise->delete();
         return response()->json(['message' => 'Devise deleted successfully'], 200);
     }
 }
 