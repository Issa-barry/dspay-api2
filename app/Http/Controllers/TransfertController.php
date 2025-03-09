<?php

namespace App\Http\Controllers;

use App\Models\Transfert;
use App\Models\Devise;
use App\Models\TauxEchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\TransfertNotification;
use App\Mail\TransfertRetireNotification;
use App\Models\Facture;
use Illuminate\Support\Facades\Mail;
use Exception;

class TransfertController extends Controller
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
     * Valider un retrait de transfert.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validerRetrait(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'code' => 'required|string|exists:transferts,code',
        ]);

        if ($validated->fails()) {
            return $this->responseJson(false, 'Validation échouée.', $validated->errors(), 422);
        }

        // Recherche du transfert avec le code
        $transfert = Transfert::where('code', $request->code)->first();

        if (!$transfert) {
            return $this->responseJson(false, 'Transfert non trouvé.', null, 404);
        }

        // Utilisation de switch pour gérer les messages selon le statut du transfert
        switch ($transfert->statut) {
            case 'retiré':
                return $this->responseJson(false, 'Ce transfert a déjà été retiré.', null);

            case 'annulé':
                return $this->responseJson(false, 'Ce transfert a été annulé et ne peut pas être retiré.', null);

            default:
                // Si le statut est valide pour le retrait
                // Mettre à jour le statut du transfert
                $transfert->statut = 'retiré';
                $transfert->save();

                // Envoi de la notification
                Mail::to($transfert->expediteur_email)
                    ->send(new TransfertRetireNotification($transfert));

                return $this->responseJson(true, 'Retrait effectué avec succès.', $transfert);
        }
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
