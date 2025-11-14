<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VoteController extends Controller
{
    /**
     * Afficher la liste de tous les votes (ADMIN)
     */
    public function index()
    {
        $votes = Vote::with(['candidate', 'event'])->get();

        return response()->json([
            'status' => 'success',
            'count' => $votes->count(),
            'votes' => $votes
        ]);
    }

    /**
     * Enregistrer un nouveau vote pour un candidat
     * Endpoint: POST /api/candidates/{candidatId}/votes
     */
    public function store(Request $request, $candidatId)
    {
        // 1. Vérifier si le candidat existe avec son événement
        $candidate = Candidate::with('event')->find($candidatId);

        if (!$candidate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Candidate not found.'
            ], 404);
        }

        // Récupération du montant d’un vote défini par l’événement
        $voteUnitPrice = $candidate->event->vote_amount;

        // 2. Validation des données
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'voting_name' => 'required|string|max:255',
            'votes_number' => 'required|integer|min:1',
            'payment_method' => ['required', Rule::in(['mix_by_yas', 'flooz'])],
            'phone_number' => 'required|string|max:20',
        ]);

        // Ajout d'une condition personnalisée : montant exact
        $validator->after(function ($validator) use ($request, $voteUnitPrice) {
            if ($request->votes_number * $voteUnitPrice != $request->amount) {
                $validator->errors()->add('amount', "The amount must be equal to votes_number × vote_amount.");
            }
        });

        // En cas d’erreur
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            DB::beginTransaction();

            // 3. Création du vote
            $vote = Vote::create([
                'event_id' => $candidate->event_id,
                'candidate_id' => $candidate->id,
                'amount' => $validated['amount'],
                'voting_name' => $validated['voting_name'],
                'votes_number' => $validated['votes_number'],
                'payment_method' => $validated['payment_method'],
                'phone_number' => $validated['phone_number'],
            ]);

            // 4. Mise à jour du nombre total de votes
            $candidate->votes_count += $validated['votes_number'];
            $candidate->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Vote registered successfully.',
                'vote' => $vote
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the vote.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
