<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    // Méthode pour afficher la page d'accueil de l'administrateur
    public function index(){
        return view('admin.dashboard');

        // Récupération de l'utilisateur administrateur connecté
       // $admin = Auth::guard('admin')->user();

        // Affichage du message de bienvenue et lien de déconnexion
       // echo 'Bienvenue ' . $admin->name . ' <a href="' . route('admin.logout') . '">Déconnexion</a>';
    }
    
    // Méthode pour effectuer la déconnexion de l'administrateur
    public function logout(){
       
        // Déconnexion de l'utilisateur administrateur
        Auth::guard('admin')->logout();
        
        // Redirection vers la page de connexion de l'administrateur
        return redirect()->route('admin.login');
    }
}
