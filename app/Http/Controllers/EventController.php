<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
    */
    
    // Lister tous les événements
    public function index()
    {
        $events = Event::all();

        return response()->json([
            'success' => true,
            'data' => $events
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
    */

    //  Créer un nouvel événement
    public function store(Request $request)
    {
        // Validation des données de la requête
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'vote_amount' => 'nullable|string',
            'status' => 'sometimes|in:en_cours,annule,reporte,termine'
        ]);

        try {
            // Création de l'événement avec les données validées
            $event = Event::create($validated);

            // Réponse en cas de succès
            return response()->json([
                'success' => true,
                'message' => 'Événement créé avec succès',
                'data' => $event
            ], 201);

        
        } catch (\Exception $ex) {
            // Réponse en cas d'erreur
            return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création',
            'error' => $ex->getMessage()
        ], 500);

        }
    }
    
    /**
    * Display the specified resource.
    */

    //  Afficher un événement par ID
    public function show(Event $event)
    {
        // Réponse avec les données de l'événement
        try {
            return response()->json([
            'success' => true,
            'data' => $event
        ], 200);//code...
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération',
                'error' => $th->getMessage()
            ], 500);
            //throw $th;
        }
    }

    /**
    * Update the specified resource in storage.
    */

    //  Mettre à jour un événement
   public function update(Request $request, Event $event)
    {
        // Validation des données de la requête
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'vote_amount' => 'nullable|string',
            'status' => 'sometimes|in:en_cours,annule,reporte,termine'
        ]);

        try {
            // Mise à jour de l'événement avec les données validées
            $event->update($validated);

            // Réponse en cas de succès
            return response()->json([
                'success' => true,
                'message' => 'Événement mis à jour avec succès',
                'data' => $event
            ], 200);

        
        } catch (\Exception $ex) {
            // Réponse en cas d'erreur
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    /**
    * Remove the specified resource from storage.
    */

    //  Supprimer un événement
    public function destroy(Event $event)
    {
        // Suppression de l'événement
        $event->delete();
        
        // Réponse en cas de succès
        return response()->json([
            'success' => true,
            'message' => 'Événement supprimé avec succès'
        ], 200);
    }
}
