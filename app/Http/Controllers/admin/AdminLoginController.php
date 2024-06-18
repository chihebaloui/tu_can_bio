<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class AdminLoginController extends Controller
{
    // Méthode pour afficher la vue de connexion de l'administrateur
    public function index(){
        return view('admin.login');
    }

    // Méthode pour authentifier l'administrateur
    public function authenticate(Request $request){
    
        // Validation des données de la requête
        $validator=Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required'
        ]);
        
        // Si la validation réussit
        if($validator->passes()){
            // Tentative d'authentification avec les informations fournies
            if(Auth::guard('admin')->attempt(['email'=>$request->email,'password'=>
            $request->password],$request->get('remember'))){

                // Récupération de l'utilisateur administrateur authentifié
                $admin=Auth::guard('admin')->user();

                // Vérification du rôle de l'administrateur
                if($admin->role==2 ){
                    // Redirection vers le tableau de bord de l'administrateur
                    return redirect()->route('admin.dashboard');
                }
                else {
                    // Déconnexion et redirection avec un message d'erreur si l'administrateur n'est pas autorisé
                    Auth::guard('admin')->logout();
                    return redirect()->route('admin.login')->with('error','Vous n\'êtes pas autorisé à accéder au panneau d\'administration.');
                }

                // Redirection vers le tableau de bord de l'administrateur
                return redirect()->route('admin.dashboard');
                
            } else {
                // Redirection avec un message d'erreur si l'authentification échoue
                return redirect()->route('admin.login')->with('error','Adresse e-mail ou mot de passe incorrect');
            }

        } else {
            // Redirection avec les erreurs de validation et les données de la requête entrées
            return redirect()->route('admin.login')
            ->withErrors($validator)
            ->withInput($request->only('email'));
        }
    }

 
}
