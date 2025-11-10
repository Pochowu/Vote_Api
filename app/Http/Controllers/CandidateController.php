<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Event;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $candidates = Candidate::all();
        return view('candidates.index',[
            'candidates' => $candidates,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('candidates.create',[
         'events' =>Event::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
          'name' => 'required',
          'description' => 'required',
          'photo' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        
        if ($request->hasFile('photo')) {
            $validated ['photo'] = $request->file('photo')->store('candidates', 'public');
        }

        Candidate::create([
            'name' =>$request->name,
            'event_id' =>$request->Event_id,
            'description' =>$request->description,
            'photo' =>$request->photo,
            'votes_count' =>$request->votes_count,
        ]);
         return redirect()->route('candidates.index')->with('success',"candidat ajoutée avec succes ");
    }
 
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
            $candidate =Candidate::find($id);
        return view('candidates.show',[
            'candidate' => $candidate,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $candidate =Candidate::find($id);
        return view('candidates.edit',[
            'candidate' => $candidate,
             'events'=>Event::all()
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
      

     
   $request->validate([
            'name' => 'required|max:225',
            'photo' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

       Candidate::find($id)->update([
        'name'=>$request->name,
        'category_id' =>$request->category_id,
        'description'=>$request->description,
        'photo'=>$request->photo,
        'votes_count'=>$request->votes_count,


       ]);
        if ($request->hasFile('photo')) {
            $validated ['photo'] = $request->file('photo')->store('candidates', 'public');
        }

     
       

      
       return back()->with('success',"candidat mis a jour avec succés");

       
 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
           {
      Candidate::find($id)->delete();
      return redirect()->route('candidates.index')->with('success',"candidat supprimée  avec success");
    }
    }
}
