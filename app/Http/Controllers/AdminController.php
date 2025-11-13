<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Gère la connexion d'un administrateur et la création d'un token Sanctum.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validation des données d'entrée
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Recherche de l'administrateur par email
        $admin = Admin::where('email', $request->email)->first();

        // Vérification du mot de passe
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        // Création du token d'API
        $token = $admin->createToken('api-token')->plainTextToken;

        // Retourne l'administrateur et le token
        return response()->json(['admin' => $admin, 'token' => $token]);
    }

    /**
     * Gère la déconnexion d'un administrateur en supprimant le token courant.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Révocation du token de l'utilisateur authentifié
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Déconnecté']);
    }

    /**
     * Affiche la liste de tous les administrateurs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $admins = Admin::all();
        return response()->json($admins);
    }

    /**
     * Crée un nouvel administrateur.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validation des données d'entrée
        $request->validate([
            'email' => 'required|email|unique:admins',
            'password' => 'required|min:6',
        ]);

        // Création de l'administrateur
        $admin = Admin::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($admin, 201);
    }

    /**
     * Affiche un administrateur spécifique.
     *
     * @param Admin $admin
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Admin $admin)
    {
        return response()->json($admin);
    }

    /**
     * Met à jour un administrateur spécifique.
     *
     * @param Request $request
     * @param Admin $admin
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Admin $admin)
    {
        // Validation des données d'entrée
        $request->validate([
            'email' => 'sometimes|email|unique:admins,email,' . $admin->id,
            'password' => 'sometimes|min:6',
        ]);

        // Hashage du mot de passe s'il est présent dans la requête
        if ($request->has('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        // Mise à jour de l'administrateur
        $admin->update($request->all());

        return response()->json($admin);
    }

    /**
     * Supprime un administrateur spécifique.
     *
     * @param Admin $admin
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Admin $admin)
    {
        $admin->delete();
        return response()->json(null, 204);
    }
}
