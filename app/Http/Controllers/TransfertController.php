<?php

namespace App\Http\Controllers;

use App\Models\Transfert;
use App\Models\Devise;
use App\Models\TauxEchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\TransfertNotification;
use App\Mail\TransfertRetireNotification;
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
     * Créer un transfert de devise.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation des données d'entrée
        $validated = Validator::make($request->all(), [
            'devise_source_id' => 'required|exists:devises,id',
            'devise_cible_id' => 'required|exists:devises,id',
            'montant' => 'required|numeric|min:0',
            'quartier' => 'string',
            'receveur_nom' => 'required|string',
            'receveur_prenom' => 'required|string',
            'receveur_phone' => 'required|string',
            'expediteur_nom' => 'required|string',
            'expediteur_prenom' => 'required|string',
            'expediteur_phone' => 'required|string',
            'expediteur_email' => 'required|email',
        ]);

        if ($validated->fails()) {
            return $this->responseJson(false, 'Validation échouée.', $validated->errors(), 422);
        }

        try {
            // Récupérer les devises
            $deviseSource = Devise::find($request->devise_source_id);
            $deviseCible = Devise::find($request->devise_cible_id);

            if (!$deviseSource || !$deviseCible) {
                return $this->responseJson(false, 'Devise source ou cible introuvable.', null, 404);
            }

            // Récupérer le taux de change
            $tauxEchange = TauxEchange::where('devise_source_id', $request->devise_source_id)
                ->where('devise_cible_id', $request->devise_cible_id)
                ->first();

            if (!$tauxEchange) {
                return $this->responseJson(false, 'Taux de change introuvable pour ces devises.', null, 404);
            }

            // Vérifier que le taux de change est bien défini
            if (is_null($tauxEchange->taux) || $tauxEchange->taux <= 0) {
                return $this->responseJson(false, 'Le taux de change est invalide.', null, 400);
            }

            // Calculer le montant converti
            $montantConverti = $request->montant * $tauxEchange->taux;

            // Créer le transfert
            $transfert = Transfert::create([
                'devise_source_id' => $request->devise_source_id,
                'devise_cible_id' => $request->devise_cible_id,
                'montant' => $request->montant,
                'montant_converti' => $montantConverti,
                'quartier' => $request->quartier,
                'receveur_nom' => $request->receveur_nom,
                'receveur_prenom' => $request->receveur_prenom,
                'receveur_phone' => $request->receveur_phone,
                'expediteur_nom' => $request->expediteur_nom,
                'expediteur_prenom' => $request->expediteur_prenom,
                'expediteur_phone' => $request->expediteur_phone,
                'expediteur_email' => $request->expediteur_email,
                'taux_echange_id' => $tauxEchange->id,
                'statut' => 'en_cours',
                'code' => (string) \Str::uuid(), // Générer un UUID comme code
            ]);

            // Charger les relations
            $transfert->load([
                'deviseSource',
                'deviseCible',
                'tauxEchange' // Charger le taux de change
            ]);

            // Envoyer un email à l'expéditeur
            Mail::to($transfert->expediteur_email)->send(new TransfertNotification($transfert));

            // Masquer le code avant de renvoyer la réponse
            $transfert->makeHidden('code');

            return $this->responseJson(true, 'Transfert effectué avec succès.', $transfert, 201);
        } catch (Exception $e) {
            return $this->responseJson(false, 'Une erreur est survenue lors de la création du transfert.', $e->getMessage(), 500);
        }
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
