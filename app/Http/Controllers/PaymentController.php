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
        FedaPay::setApiKey(env('sk_sandbox_MN6v2i8ONTKgi31_PdFwIr1H'));
        FedaPay::setEnvironment(env('sandbox'));
    }

    /**
     * Initier un paiement pour un vote
     */
    public function initiatePayment(Request $request): JsonResponse
    {
        $request->validate([
            'vote_id' => 'required|exists:votes,id',
            'amount' => 'required|numeric',
            'candidate_id' => 'required|exists:candidates,id',
            'full_name' => 'required|string',
            'phone_number' => 'required|string',
            'vote_number' => 'required|integer|min:1',
            'currency' => 'required|in:XOF,USD,EUR',
            //'callback_url' => 'required|url',
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
                    'email' => 'kokoubernardabeni@gmail.com', // À adapter
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
                'amount' => $request->amount,
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
        $request->validate([
            'reference' => 'required|string',
            'status' => 'required|in:approved,canceled,declined'
        ]);

        try {
            $vote = Vote::where('payment_reference', $request->reference)->firstOrFail();

            if ($request->status === 'approved') {
                $vote->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed'
                ]);

                // Incrémenter le nombre de votes du candidat
                $vote->candidate->increment('votes_count', $vote->votes_number);
            } else {
                $vote->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled'
                ]);
            }

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
        // Vérifier la signature du webhook
        $signature = $request->header('X-FedaPay-Signature');

        // Traiter l'événement
        $event = $request->all();

        if ($event['name'] === 'transaction.approved') {
            $transaction = $event['data']['transaction'];

            $vote = Vote::where('payment_reference', $transaction['reference'])->first();

            if ($vote) {
                $vote->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed'
                ]);

                $vote->candidate->increment('votes_count', $vote->votes_number);
            }
        }

        return response()->json(['success' => true]);
    }
}
