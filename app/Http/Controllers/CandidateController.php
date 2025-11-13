<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    /**
     * Affiche la liste de tous les candidats avec leurs événements associés.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Récupérer tous les candidats avec leurs événements associés
        $candidates = Candidate::with('event')->get();

        return response()->json([
            'status' => 'success',
            'data' => $candidates,
        ]);
    }

    /**
     * Affiche la liste des candidats pour un événement spécifique.
     *
     * @param int $event_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByEvent($event_id)
    {
        $candidates = Candidate::where('event_id', $event_id)->with('event')->get();

        return response()->json([
            'status' => 'success',
            'data' => $candidates,
        ]);
    }

    /**
     * Crée un nouveau candidat.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validation des données d'entrée
        $validated = $request ->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'required|string|max:5100', // URL ou nom de l'image
        ]);

        // Création du candidat
        $candidate = Candidate::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Candidat créé avec succès',
            'data' => $candidate,
        ], 201);
    }

    /**
     * Affiche un candidat spécifique.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Recherche du candidat avec son événement associé
        $candidate = Candidate::with('event')->find($id);

        // Vérification de l'existence du candidat
        if (!$candidate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Candidat introuvable',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $candidate,
        ]);
    }

    /**
     * Met à jour un candidat spécifique.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Recherche du candidat
        $candidate = Candidate::find($id);

        // Vérification de l'existence du candidat
        if (!$candidate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Candidat introuvable',
            ], 404);
        }

        // Validation des données d'entrée
        $validated = $request->validate([
            'event_id' => 'sometimes|exists:events,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'sometimes|string|max:5100',
            'votes_count' => 'sometimes|integer'
        ]);

        // Mise à jour du candidat
        $candidate->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Candidat mis à jour avec succès',
            'data' => $candidate,
        ]);
    }

    /**
     * Supprime un candidat spécifique.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Recherche du candidat
        $candidate = Candidate::find($id);

        // Vérification de l'existence du candidat
        if (!$candidate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Candidat introuvable',
            ], 404);
        }

        // Suppression du candidat
        $candidate->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Candidat supprimé avec succès',
        ]);
    }
}