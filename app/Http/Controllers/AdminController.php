<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Afficher la liste des admins
    public function index()
    {
        $admins = Admin::all();
        return response()->json($admins);
    }

    // Créer un nouvel admin
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:admins',
            'password' => 'required|min:6',
        ]);

        $admin = Admin::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($admin, 201);
    }

    // Afficher un admin spécifique
    public function show(Admin $admin)
    {
        return response()->json($admin);
    }

    // Mettre à jour un admin
    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'email' => 'sometimes|email|unique:admins,email,' . $admin->id,
            'password' => 'sometimes|min:6',
        ]);

        if ($request->has('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        $admin->update($request->all());

        return response()->json($admin);
    }

    // Supprimer un admin
    public function destroy(Admin $admin)
    {
        $admin->delete();
        return response()->json(null, 204);
    }
}
