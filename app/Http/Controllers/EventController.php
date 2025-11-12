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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'vote_amount' => 'required|string',
            'status' => 'required|string'
        ]);

        try {
           $data = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'vote_amount' => $request->input('vote_amount'),
                'status' => $request->input('status'),
            ];

            $events = Event::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Événement créé avec succès',
                'data' => $events
            ], 201);

        
        } catch (\Exception $ex) {
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
    public function show(string $id)
    {
        $events = Event::find($id);

        if (!$events) {
            return response()->json([
                'success' => false,
                'message' => 'Événement non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $events
        ], 200);
    }

    /**
    * Update the specified resource in storage.
    */

    //  Mettre à jour un événement
   public function update(Request $request, string $id)
    {
        $events = Event::find($id);

        if (!$events) {
            return response()->json([
                'success' => false,
                'message' => 'Événement non trouvé'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'vote_amount' => 'required|string',
            'status' => 'required|string'
        ]);

        try {
           $data = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'vote_amount' => $request->input('vote_amount'),
                'status' => $request->input('status'),
            ];

            $events->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Événement mis à jour avec succès',
                'data' => $events
            ], 201);

        
        } catch (\Exception $ex) {
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
    // public function destroy($id)
    // {
    //     $events = Event::find($id);

    //     if (!$events) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Événement non trouvé'
    //         ], 404);
    //     }

    //     $events->delete();
        
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Événement supprimé avec succès'
    //     ], 200);
    // }

    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Événement non trouvé ❌'
            ], 404);
        }

        $event->delete(); // grâce à onDelete('cascade'), les candidats seront aussi supprimés

        return response()->json([
            'success' => true,
            'message' => 'Événement supprimé avec succès 🗑️'
        ], 200);
    }
}
