<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Configuration de FedaPay
        FedaPay::setApiKey(config('services.fedapay.secret_key'));
        FedaPay::setEnvironment(config('services.fedapay.environment', 'sandbox'));
    }

    /**
     * Initier un paiement pour un vote
     */
    public function initiatePayment(Request $request): JsonResponse
    {
        $request->validate([
            'vote_id' => 'required|exists:votes,id',
            'amount' => 'required|numeric',
            'currency' => 'required|in:XOF,USD,EUR',
            'callback_url' => 'required|url',
        ]);

        try {
            $vote = Vote::find($request->vote_id);

            $transaction = Transaction::create([
                'description' => 'Paiement pour vote - ' . $vote->voting_name,
                'amount' => $request->amount,
                'currency' => ['iso' => $request->currency],
                'callback_url' => $request->callback_url,
                'customer' => [
                    'firstname' => $vote->voting_name,
                    'lastname' => 'Voter',
                    'email' => 'voter@example.com', // À adapter
                    'phone_number' => [
                        'number' => $vote->phone_number,
                        'country' => 'TG' // Togo par défaut
                    ]
                ]
            ]);

            // Sauvegarder la référence du paiement
            $vote->update([
                'payment_reference' => $transaction->reference,
                'payment_status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'payment_url' => $transaction->generateToken(),
                'reference' => $transaction->reference,
                'message' => 'Paiement initié avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initiation du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Callback après paiement
     */
    public function paymentCallback(Request $request): JsonResponse
    {
        // Validation de la requête de callback
        $request->validate([
            'reference' => 'required|string',
            'status' => 'required|in:approved,canceled,declined'
        ]);

        try {
            // Récupération du vote par sa référence de paiement
            $vote = Vote::where('payment_reference', $request->reference)->firstOrFail();

            // Mise à jour du statut du vote
            $this->updateVoteStatus($vote, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Statut du paiement mis à jour'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du callback: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour le statut d'un vote en fonction du statut du paiement.
     *
     * @param Vote $vote
     * @param string $paymentStatus
     * @return void
     */
    private function updateVoteStatus(Vote $vote, string $paymentStatus): void
    {
        if ($paymentStatus === 'approved') {
            // Si le paiement est approuvé, on met à jour le statut du vote et on incrémente les votes du candidat
            $vote->update([
                'payment_status' => 'paid',
                'status' => 'confirmed'
            ]);

            $vote->candidate->increment('votes_count', $vote->votes_number);
        } else {
            // Si le paiement a échoué ou a été annulé, on met à jour le statut du vote
            $vote->update([
                'payment_status' => 'failed',
                'status' => 'cancelled'
            ]);
        }
    }

    /** Vérifier le statut d'un paiement */
    public function checkPaymentStatus($reference): JsonResponse
    {
        try {
            $transaction = Transaction::retrieve($reference);
            $vote = Vote::where('payment_reference', $reference)->firstOrFail();

            return response()->json([
                'success' => true,
                'payment_status' => $transaction->status,
                'vote_status' => $vote->payment_status,
                'transaction' => $transaction->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook FedaPay pour les notifications
     */
    public function webhookHandler(Request $request): JsonResponse
    {
        // Vérifier la signature du webhook pour la sécurité (recommandé en production)
        // $signature = $request->header('X-FedaPay-Signature');
        // Webhook::constructEvent($request->getContent(), $signature, config('services.fedapay.secret_key'));

        $event = $request->all();

        // On traite l'événement uniquement si c'est une transaction approuvée
        if (isset($event['name']) && $event['name'] === 'transaction.approved') {
            $transaction = $event['data']['transaction'];
            $vote = Vote::where('payment_reference', $transaction['reference'])->first();

            if ($vote) {
                // On met à jour le statut du vote en utilisant la méthode privée
                $this->updateVoteStatus($vote, 'approved');
            }
        }

        return response()->json(['success' => true]);
    }
}
