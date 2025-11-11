<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    /**
     * Lister tous les candidats
     */
    public function index()
    {
        $candidates = Candidate::with('event')->get();

        return response()->json([
            'status' => 'success',
            'data' => $candidates,
        ]);
    }

    /**
     *  Créer un nouveau candidat
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'required|string|max:5100', // ici on attend juste une URL ou un nom d'image
            'votes_count' => 'required|int'
        ]);

        $candidate = Candidate::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Candidat créé avec succès ',
            'data' => $candidate,
        ], 201);
    }

    /**
     * 🔍 Afficher un candidat spécifique
     */
    public function show($id)
    {
        $candidate = Candidate::with('event')->find($id);

        if (!$candidate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Candidat introuvable ',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $candidate,
        ]);
    }

    /**
     *  Mettre à jour un candidat
     */
    public function update(Request $request, $id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Candidat introuvable ',
            ], 404);
        }

        $validated = $request->validate([
            'event_id' => 'sometimes|exists:events,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'required|string|max:5100',
            'votes_count' => 'required|int'
        ]);

        $candidate->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Candidat mis à jour avec succès ',
            'data' => $candidate,
        ]);
    }

    /**
     *  Supprimer un candidat
     */
    public function destroy($id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Candidat introuvable ',
            ], 404);
        }

        $candidate->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Candidat supprimé avec succès ',
        ]);
    }
}