<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use Illuminate\Http\Request;
use App\Models\Candidate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VoteController extends Controller
{
    public function index()
    {
        $votes = Vote::with(['candidate', 'event'])->get();
        return response()->json($votes);
    }
    /**
     * Enregistre un nouveau vote pour un candidat donné.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // 1. Validation des données de la requête
        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'amount' => 'required|string',
            'voting_name' => 'required|string|max:255',
            'votes_number' => 'required|integer|min:1',
            'payment_method' => ['required', Rule::in(['mix_by_yas', 'flooz'])],
            'phone_number' => 'required|string|max:255',
        ]);

        // 2. Récupération du candidat
        $candidate = Candidate::findOrFail($request->input('candidate_id'));

        // 3. Utilisation d'une transaction pour garantir l'intégrité des données
        try {
            DB::beginTransaction();

            // Création de l'enregistrement de vote
            $vote = Vote::create([
                'event_id' => $candidate->event_id,
                'candidate_id' => $candidate->id,
                'amount' => $request->amount,
                'voting_name' => $request->voting_name,
                'votes_number' => $request->votes_number,
                'payment_method' => $request->payment_method,
                'phone_number' => $request->phone_number,
            ]);

            // Incrémentation du compteur de votes du candidat
            $candidate->increment('votes_count', $request->votes_number);

            DB::commit();

            // 4. Réponse en cas de succès
            return response()->json([
                'message' => 'Vote enregistré et compté avec succès.',
                'vote' => $vote
            ], 201);

        } catch (\Exception $e) {
            // En cas d'erreur, on annule la transaction
            DB::rollBack();
            // Et on retourne une réponse d'erreur
            return response()->json(['message' => 'Erreur lors de l\'enregistrement du vote.', 'error' => $e->getMessage()], 500);
        }
    }
}
