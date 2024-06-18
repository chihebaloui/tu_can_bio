<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontController extends Controller
{
    public function index (){

        $products = Product::where('is_featured', 'Yes')
        ->orderBy('id','ASC')
        ->where('status', 1)
        ->take(8)
        ->get();
        $data['featuredProducts'] = $products;

        $latestproducts = Product::orderBy('id','ASC')
        ->where('status',1)
        ->take(8)
        ->get();
        $data['latestProducts']=$latestproducts;
        return view('front.home', $data);
        


}

public function addToWishlist(Request $request){
    if (Auth::check()==false){

        session(['url.intended'=>url()->previous()]);
        return response()->json([
            'status'=>false
        ]);
    }

   

    $product=Product::where('id',$request->id)->first();

    if($product==null) {
        return response()->json([
            'status'=>true,
            'message'=>'<div class="alert alert-danger">product not found</div>'
        ]);
    }

    wishlist::updateOrCreate([
        'user_id'=>Auth::user()->id,
        'product_id'=>$request->id,
    ],
    [
        'user_id'=>Auth::user()->id,
        'product_id'=>$request->id,
    ]
);

    return response()->json([
        'status' => true,
        'message' => '<div class="alert alert-success"><strong>"' . e($product->title) . '"</strong> added to your wishlist</div>'
    ]);
    
}


}
