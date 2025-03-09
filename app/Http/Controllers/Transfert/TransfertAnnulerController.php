<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use App\Models\Transfert;
use Illuminate\Http\Request;

class TransfertAnnulerController extends Controller
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
     * Annuler un transfert existant.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function annulerTransfert($id)
    {
        $transfert = Transfert::find($id);

        if (!$transfert) {
            return $this->responseJson(false, 'Transfert non trouvé.', null, 404);
        }

        if ($transfert->statut !== 'en_cours') {
            return $this->responseJson(false, 'Seuls les transferts en cours peuvent être annulés.', null, 400);
        }

        $transfert->update([
            'statut' => 'annulé',
        ]);

        return $this->responseJson(true, 'Transfert annulé avec succès.');
    }

}
