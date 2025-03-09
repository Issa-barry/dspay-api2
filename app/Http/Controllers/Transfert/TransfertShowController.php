<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use App\Models\Transfert;
use Exception;
use Illuminate\Http\Request;

class TransfertShowController extends Controller
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

    /**
     * Afficher tous les transferts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $transferts = Transfert::with(['deviseSource', 'deviseCible', 'tauxEchange'])->get();
            
            // Masquer le code si le statut est 'en_cours' pour chaque transfert
            foreach ($transferts as $transfert) {
                if ($transfert->statut === 'en_cours') {
                    $transfert->makeHidden('code');
                }
            }

            return $this->responseJson(true, 'Liste des transferts récupérée avec succès.', $transferts);
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la récupération des transferts.', $e->getMessage(), 500);
        }
    }

    /**
     * Afficher un transfert spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $transfert = Transfert::with(['deviseSource', 'deviseCible', 'tauxEchange'])->find($id);

            if (!$transfert) {
                return $this->responseJson(false, 'Transfert non trouvé.', null, 404);
            }

            // Masquer le code si le statut n'est pas "retiré"
            if ($transfert->statut === 'en_cours') {
                $transfert->makeHidden('code');
            }

            return $this->responseJson(true, 'Transfert récupéré avec succès.', $transfert);
        } catch (Exception $e) {
            return $this->responseJson(false, 'Erreur lors de la récupération du transfert.', $e->getMessage(), 500);
        }
    }
}
