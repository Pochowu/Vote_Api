<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use Illuminate\Http\Request;
use App\Models\Candidate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VoteController extends Controller
{
    /**
     * Enregistre un nouveau vote pour un candidat donné.
     * Endpoint: POST /api/candidates/{candidatId}/votes
     */
    public function store(Request $request, $candidatId)
    {
        // 1. Validation des données
        $request->validate([
            'amount' => 'required|string',
            'voting_name' => 'required|string|max:255',
            'votes_number' => 'required|integer|min:1',
            'payment_method' => ['required', Rule::in(['mix_by_yas', 'flooz'])],
            'phone_number' => 'nullable|string|max:255',
        ]);

        // 2. Vérification du Candidat
        $candidat = Candidate::findOrFail($candidatId);

        // 3. Logique de Transaction
        try {
            DB::beginTransaction();

            // Création de l'entrée de vote
            $vote = Vote::create([
                'event_id' => $candidat->event_id, // Récupération automatique de l'event_id
                'candidat_id' => $candidatId,
                'amount' => $request->amount,
                'voting_name' => $request->voting_name,
                'votes_number' => $request->votes_number,
                'payment_method' => $request->payment_method,
                'phone_number' => $request->phone_number,
            ]);

            // Mise à jour du compteur de votes du candidat
            $candidat->votes_count += $request->votes_number;
            $candidat->save();

            DB::commit();

            // 4. Réponse
            return response()->json([
                'message' => 'Vote enregistré et compté avec succès.',
                'vote' => $vote
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de l\'enregistrement du vote.', 'error' => $e->getMessage()], 500);
        }
    }
}
