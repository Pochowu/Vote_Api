<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    /**
     * 📋 Afficher la liste des admins
     */
    public function index()
    {
        $admins = Admin::all();
        return response()->json($admins);
    }

    /**
     * ➕ Créer un nouvel admin
     */
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

        return response()->json([
            'status' => 'success',
            'message' => 'Admin créé avec succès ✅',
            'data' => $admin
        ], 201);
    }

    /**
     * 👤 Afficher un admin spécifique
     */
    public function show(Admin $admin)
    {
        return response()->json($admin);
    }

    /**
     * ✏️ Mettre à jour un admin
     */
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

        return response()->json([
            'status' => 'success',
            'message' => 'Admin mis à jour avec succès ✅',
            'data' => $admin
        ]);
    }

    /**
     * 🗑️ Supprimer un admin
     */
    public function destroy(Admin $admin)
    {
        $admin->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Admin supprimé avec succès 🗑️',
        ]);
    }

    /**
     * 🔐 Connexion de l'admin (sans token)
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $admin = Admin::where('email', $validated['email'])->first();

        if (!$admin || !Hash::check($validated['password'], $admin->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email ou mot de passe incorrect ❌',
            ], 401);
        }

        // Stocker les infos de session
        Session::put('admin_id', $admin->id);
        Session::put('admin_email', $admin->email);

        return response()->json([
            'status' => 'success',
            'message' => 'Connexion réussie ✅',
            'admin' => [
                'id' => $admin->id,
                'email' => $admin->email,
            ],
        ]);
    }

    /**
     * 🚪 Déconnexion (suppression de session)
     */
    public function logout()
    {
        Session::forget(['admin_id', 'admin_email']);
        Session::flush();

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie 👋',
        ]);
    }

    /**
     * 🔍 Vérifier si un admin est connecté
     */
    public function checkLogin()
    {
        if (Session::has('admin_id')) {
            return response()->json([
                'status' => 'connected',
                'admin_email' => Session::get('admin_email'),
            ]);
        }

        return response()->json([
            'status' => 'disconnected',
        ]);
    }
}