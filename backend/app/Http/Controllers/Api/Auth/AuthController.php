<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseController
{
    /**
     * Register new user (client only)
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'required|string|unique:users,telephone',
            'password' => 'required|string|min:6|confirmed',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Créer l'utilisateur
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'statut' => 'inactif', // En attente validation agent
        ]);

        // Créer le profil client
        Client::create([
            'user_id' => $user->id,
            'adresse' => $request->adresse,
            'ville' => $request->ville,
            'type_client' => 'particulier',
        ]);

        return $this->sendResponse([
            'user' => $user,
        ], 'Inscription réussie. Votre compte sera activé par un agent.', 201);
    }

    /**
     * Login user and return JWT token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return $this->sendError('Email ou mot de passe incorrect', [], 401);
        }

        // Vérifier si le compte est actif
        if (auth('api')->user()->statut !== 'actif') {
            auth('api')->logout();
            return $this->sendError('Votre compte est inactif. Contactez un administrateur.', [], 403);
        }

        // Mettre à jour la dernière connexion
        auth('api')->user()->update(['derniere_connexion' => now()]);

        return $this->sendResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user(),
        ], 'Connexion réussie');
    }

    /**
     * Logout user (invalidate token)
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();
        return $this->sendSuccess('Déconnexion réussie');
    }

    /**
     * Refresh JWT token
     */
    public function refresh(): JsonResponse
    {
        return $this->sendResponse([
            'access_token' => auth('api')->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], 'Token rafraîchi');
    }

    /**
     * Get authenticated user info
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();
        
        // Charger la relation selon le rôle
        switch ($user->role) {
            case 'client':
                $user->load('client');
                break;
            case 'agent':
                $user->load('agent');
                break;
            case 'technicien':
                $user->load('technicien');
                break;
            case 'manager':
                $user->load('manager');
                break;
        }

        return $this->sendResponse($user, 'Informations utilisateur');
    }

    /**
     * Forgot password (TODO: implémenter l'envoi d'email)
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // TODO: Implémenter l'envoi d'email de réinitialisation
        
        return $this->sendSuccess('Un email de réinitialisation a été envoyé');
    }

    /**
     * Reset password (TODO: implémenter la vérification du token)
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // TODO: Vérifier le token et réinitialiser le mot de passe
        
        return $this->sendSuccess('Mot de passe réinitialisé avec succès');
    }
}

