<?php

namespace App\Http\Controllers;

use App\Models\Order;
use  App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Wishlist;
use Illuminate\Validation\Rules\Password as PasswordRule;
//use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(){
        return view('front.account.login');
    }

    
    public function register(){
        return view('front.account.register');
    }


    public function registerPost(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:15',
            'password' => [
                'required',
                'string',
                'confirmed',
                PasswordRule::min(8)
                    ->mixedCase()   // Requires at least one uppercase and one lowercase letter
                    ->letters()     // Requires at least one letter
                    ->numbers()     // Requires at least one number
                    
                    ->uncompromised() // Prevents compromised passwords
            ], [
                'password.min' => 'Password must be at least 8 characters long.',
                'password.mixedCase' => 'Password must contain both uppercase and lowercase letters.',
                'password.letters' => 'Password must include at least one letter.',
                'password.numbers' => 'Password must include at least one number.',
                
                'password.uncompromised' => 'The password has been compromised in a data breach, please choose a different password.',
            ]
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('account.login')->with('success', 'Register successfully');

    }


    public function loginPost(Request $request){

        $validator = Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required',
        ]);

        if($validator ->passes()){

            if(Auth::attempt(['email'=>$request->email,'password'=>$request->password],$request->get('remember'))){

                // Redirection après connexion réussie
                
                if (session()->has('url.intended')) {
                    return redirect(session()->get('url.intended'));
                }


             return redirect()->route('account.profile');

            }else{
                //session()->flash('error','Either email/password is incorrect.');
                return redirect()->route('account.login')
                ->withInput($request->only('email'))
                ->with('error','Either email/password is incorrect');
            }

        }else{
            return redirect()->route('account.login')
            ->withErrors($validator)
            ->withInput($request->only('email'));
        }

    }

 public function profile(){

    return view('front.account.profile');

 }

 public function logout(Request $request){
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('account.login')
    ->with('success','you successfully logged out !');
 }

 public function orders(){

    $user = Auth::user();

    $orders = Order::where( 'user_id', $user->id)->orderBy('created_at','DESC')->get();

    $data['orders'] = $orders;

    return view('front.account.order', $data);
 }

 public  function orderDetail($id){
    $data = [];
    $user = Auth::user();
    $order = Order::where( 'user_id', $user->id)->where( 'id', $id)->first();
    $data['order'] = $order;

    $orderItems = OrderItem::where( 'order_id', $id)->get();
    $data['orderItems'] = $orderItems;
    return view('front.account.order-detail', $data);
 }


 public function wishlist(){
    $wishlists=Wishlist::where('user_id',Auth::user()->id)->get();
    $data=[];
    $data['wishlists']=$wishlists;
    return view('front.account.wishlist',$data);



 }
 public function removeProductFromWishlist(Request $request){
    $wishlist=Wishlist::where('user_id',Auth::user()->id)->where('product_id',$request->id)->first();
    if($wishlist ==null){
        session()->flash('error','product aalready removed');
        return response()->json([
            'status'=>true,
        ]);
    }else{
        wishlist::where('user_id',Auth::user()->id)->where('product_id',$request->id)->delete();
        session()->flash('success','product removed successfully');
        return response()->json([
            'status'=>true,
        ]);
    }
 }

}
