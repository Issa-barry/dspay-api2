<?php
 namespace App\Http\Controllers;

 use App\Http\Controllers\Controller;
 use App\Models\Devise;
use Exception;
use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Auth;
 
 class DeviseController extends Controller
 {
      /**
     * Fonction pour centraliser les réponses JSON
     */
    protected function responseJson($success, $message, $data = null, $statusCode = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    } 

    
     public function index()
     {
        $devise =  Devise::all();
        return response()->json([
            'success' => true,
            'message' => 'Liste des Devises récupérée avec succès.',
            'data' => $devise
        ]);
 
     }
 

    public function store(Request $request)
        {
            try {
                $validated = $request->validate([
                    'nom' => 'required|string|max:255|unique:devises',
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
      
 
    //  public function destroy($id)
    //  {
    //      $devise = Devise::find($id);
    //      if (!$devise) {
    //          return response()->json(['message' => 'Devise not found'], 404);
    //      }
 
    //      $devise->delete();
    //      return response()->json(['message' => 'Devise deleted successfully'], 200);
    //  }
     /**
     * Supprimer un taux de change.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $devise = Devise::find($id);

            if (!$devise) {
                return $this->responseJson(false, 'Taux de change non trouvé.', null, 404);
            }

            $devise->delete();

            return $this->responseJson(true, 'Taux de change supprimé avec succès.');
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la suppression du taux de change.', $e->getMessage(), 500);
        }
    }
 }
   