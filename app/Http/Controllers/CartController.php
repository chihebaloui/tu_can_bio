<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\DiscountCoupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingCharge;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use  Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request){
      //  Cart::add('293ad', 'Product 1', 1, 9.99);
      $product = Product::with('product_images')->find($request->id);
      if($product == null){
        return response()->json([
            'status'=>false,
            'message'=>'Recored not found'      
          ]);
      }

      if(Cart::count()>0){

        // products found in cart 
        // check in this product already in the cart 
        // return as message that product already added in you cart 
        // if product not fount in the cart , then added product in cart 

        $cartContent= Cart ::content();
        $productAlreadyExist=false;

        foreach($cartContent as $item){
            if($item->id == $product->id){
                $productAlreadyExist=true;
            }
        }


        if($productAlreadyExist==false){
            Cart::add($product->id,$product->title,1,$product
            ->price,['productImage'=>(!empty($product->product_images)) ? $product->product_images
            ->first():'']);

            $status=true;
            $message='<strong>'.$product->title.'</strong> added in cart successfully';
            session()->flash('success', $message);

        }else 
        {
            $status=false;
            $message=$product->title.' already added to cart';
        }


      }else{

    
        Cart::add($product->id,$product->title,1,$product->price,['productImage'=>(!empty($product->product_images)) ? $product->product_images->first():'']);
        $status=true;
        $message='<strong>'.$product->title.'</strong> added in cart successfully';
        session()->flash('success', $message);
      }

      return response()->json([
        'status'=>$status,
        'message'=>$message      
      ]);




    }

    public function cart(){
        $cartContent=Cart::content();
        $data['cartContent']=$cartContent;

       return view('front.cart',$data);
    }






    public function updateCart(Request $request){

        $rowId=$request->rowId;
        $qty=$request->qty;
        $itemInfo=Cart::get($rowId);
        $product=Product::find($itemInfo->id);

         // Check quantity available in stock
                if ($product->track_qty == 'Yes') {
                    if ($qty <= $product->qty) {
                        Cart::update($rowId, $qty);
                        $message = 'Cart updated successfully';
                        $status = true;
                        session()->flash('success', $message);
                    } else {
                        $message = 'QUANTITY (' . $qty . ') is not available in stock';
                        $status = false;
                        session()->flash('error', $message);
                    }
                } else {
                    Cart::update($rowId, $qty);
                    $message = 'Cart updated successfully';
                    $status = true;
                    session()->flash('success', $message);
                }

               

                return response()->json([
                    'status' => $status,
                    'message' => $message
                ]);

        
    
    
    }

    public function deleteItem(Request $request){
        $rowId=$request->rowId;
        $itemInfo=Cart::get($rowId);
        
        if($itemInfo == null){
            $errorMessage = 'Item not found in cart';
            session()->flash('error', $errorMessage);
            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }
        Cart::remove($request->rowId);   
        $message = 'Item removed from cart successfully.';
        session()->flash('success', $message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);

         
    }



    public function  checkout(Request $request){
        $discount=0;



        // if cart is empty redirect to cart page 
        if(Cart::count() ==0){
            return redirect()->route('front.cart');
        }

            // Si l'utilisateur n'est pas connecté, redirigez-le vers la page de connexion
        if (Auth::check() == false) {
            if (!session()->has('url.intended')) {
                session(['url.intended' => url()->current()]);
            }
                return redirect()->route('account.login');
        }

        $CustomerAddress = CustomerAddress::where('user_id',Auth::user()->id)->first();
            
            
        session()->forget('url.intended');

        $countries = Country::orderBy('name','ASC')->get();

        $subTotal=Cart::subtotal(2, '.', '');

        //Apply discount here 
        if(session()->has('code')){
            $code=session()->get('code');
            if ($code->type =='percent'){
                $discount=($code->discount_amount/100)*$subTotal;

            }else{
                $discount=$code->discount_amount;
            }
        }


        //Calculate shipping here 
        if ($CustomerAddress != '') {
            $userCountry = $CustomerAddress->country_id;
            $shippingInfo = ShippingCharge::where('country_id', $userCountry)->first();
        
            $totalQty = 0;
            $totalShippingCharge = 0;
            $grandTotal = 0;
        
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }
        
            if ($shippingInfo && isset($shippingInfo->amount)) {
                $totalShippingCharge = $totalQty * $shippingInfo->amount;
            } else {
                $totalShippingCharge = 0; // Default value if shippingInfo or amount is not set
            }
        
            $grandTotal = ($subTotal-$discount)+ $totalShippingCharge;
        } else {
            $grandTotal = ($subTotal-$discount);
            $totalShippingCharge = 0;
        }
        


        return view('front.checkout', [
            'countries' => $countries,
            'customerAddress' => $CustomerAddress,
            'totalShippingCharge' => $totalShippingCharge,
            'discount' => $discount, 
            'grandTotal' => $grandTotal
        ]);
    }

    public function processCheckout(Request $request){

        // step-1 Apply validation

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please fix the errors',
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
        

        // step 2  save user adress 
        $user=Auth::user();


        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country,
                'address' => $request->address,
                'appartment'=> $request->appartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                
            ]
            );

        //step 3 store data in orders table

        if($request->payment_method =='cod'){

             $couponCodeId=NULL;
             $couponCode='';
             $shipping =0;
             $discount=0;
             $subTotal=Cart::subtotal(2,'.','');


             //Apply discount here 
                if(session()->has('code')){
                    $code=session()->get('code');
                    if ($code->type =='percent'){
                        $discount=($code->discount_amount/100)*$subTotal;

                    }else{
                        $discount=$code->discount_amount;
                    }
                    $couponCodeId= $code->id;
                    $couponCode=$code->code;
                }


             //calculate shipping 
             $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();

             $totalQty = 0;
             foreach (Cart::content() as $item) {
                 $totalQty += $item->qty;
             }

             if($shippingInfo !=null){
                $shipping=$totalQty* $shippingInfo->amount;
                $grandTotal= ($subTotal-$discount)+$shipping;
             }else{
                $shippingInfo=ShippingCharge::where('country_id', 'rest_of_world')->first();
                $shipping=$totalQty* $shippingInfo->amount;
                $grandTotal= ( $subTotal-$discount)+$shipping;
             }


             







            $order = new Order;
            $order->subtotal= $subTotal;
            $order->shipping= $shipping;
            $order->grand_total=$grandTotal;
            $order->discount=$discount;
            $order->coupon_code_id=$couponCodeId;
            $order->coupon_code=$couponCode;
            $order->payment_status= 'not paid';
            $order->status= 'pending';
            $order->user_id=$user->id;
            $order->first_name=$request->first_name;
            $order->last_name=$request->last_name;
            $order->email=$request->email;
            $order->mobile=$request->mobile;
            $order->address=$request->address;
            $order->appartment=$request->appartment;
            $order->state=$request->state;
            $order->city=$request->city;
            $order->zip=$request->zip;
            $order->notes=$request->order_notes;
            $order->country_id= $request->country;
            $order->save();
            

            //steep  4 store order items in order_items table

           foreach(Cart::content () as $item){
            $orderItem= new OrderItem;
            $orderItem->product_id =$item->id;
            $orderItem->order_id =$order->id;
            $orderItem->name =$item->name;
            $orderItem->qty =$item->qty;
            $orderItem->price =$item->price;
            $orderItem->total =$item->price*$item->qty;
            $orderItem->save();



            // update product stock 
            $productData=Product::find($item->id);
            if($productData->track_qty=='Yes'){
                $currentQty=$productData->qty;
                $updateQty=$currentQty-$item->qty;
                $productData->qty=$updateQty;
                $productData->save();
            }
           
            

           }

           //Send order Email
           orderEmail($order->id,'customer');

           session()->flash('success','You have successfully placed  your order');

           Cart::destroy();
           session()->forget('code');
           
            return response()->json([
                'message' => 'order save successfully',
                'orderId' => $order->id,
                'status' => true
                
            ]);




        } else{ /****************************************************************************************************** */


            $couponCodeId=NULL;
             $couponCode='';
             $shipping =0;
             $discount=0;
             $subTotal=Cart::subtotal(2,'.','');


             //Apply discount here 
                if(session()->has('code')){
                    $code=session()->get('code');
                    if ($code->type =='percent'){
                        $discount=($code->discount_amount/100)*$subTotal;

                    }else{
                        $discount=$code->discount_amount;
                    }
                    $couponCodeId= $code->id;
                    $couponCode=$code->code;
                }


             //calculate shipping 
             $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();

             $totalQty = 0;
             foreach (Cart::content() as $item) {
                 $totalQty += $item->qty;
             }

             if($shippingInfo !=null){
                $shipping=$totalQty* $shippingInfo->amount;
                $grandTotal= ($subTotal-$discount)+$shipping;
             }else{
                $shippingInfo=ShippingCharge::where('country_id', 'rest_of_world')->first();
                $shipping=$totalQty* $shippingInfo->amount;
                $grandTotal= ( $subTotal-$discount)+$shipping;
             }


             







            $order = new Order;
            $order->subtotal= $subTotal;
            $order->shipping= $shipping;
            $order->grand_total=$grandTotal;
            $order->discount=$discount;
            $order->coupon_code_id=$couponCodeId;
            $order->coupon_code=$couponCode;
            $order->payment_status= 'paid';
            $order->status= 'pending';
            $order->user_id=$user->id;
            $order->first_name=$request->first_name;
            $order->last_name=$request->last_name;
            $order->email=$request->email;
            $order->mobile=$request->mobile;
            $order->address=$request->address;
            $order->appartment=$request->appartment;
            $order->state=$request->state;
            $order->city=$request->city;
            $order->zip=$request->zip;
            $order->notes=$request->order_notes;
            $order->country_id= $request->country;
            $order->save();
            

            //steep  4 store order items in order_items table

           foreach(Cart::content () as $item){
            $orderItem= new OrderItem;
            $orderItem->product_id =$item->id;
            $orderItem->order_id =$order->id;
            $orderItem->name =$item->name;
            $orderItem->qty =$item->qty;
            $orderItem->price =$item->price;
            $orderItem->total =$item->price*$item->qty;
            $orderItem->save();



            // update product stock 
            $productData=Product::find($item->id);
            if($productData->track_qty=='Yes'){
                $currentQty=$productData->qty;
                $updateQty=$currentQty-$item->qty;
                $productData->qty=$updateQty;
                $productData->save();
            }
           
            

           }

           //Send order Email
           orderEmail($order->id,'customer');

           session()->flash('success','You have successfully placed and paid your order');

           Cart::destroy();
           session()->forget('code');
           
            return response()->json([
                'message' => 'order save successfully',
                'orderId' => $order->id,
                'status' => true
                
            ]);  //000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000


            \Stripe\Stripe::setApiKey(config('stripe.sk'));

        

        $session = \Stripe\Checkout\Session::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'USD',
                        'product_data' => [
                            "name" => $orderItem->name,
                        ],
                        'unit_amount' => $grandTotal,
                    ],
                    'quantity' => $orderItem->qty,
                ],
            ],
            //'mode' => 'payment',
            'success_url' => route('success'),
            'cancel_url' => route('checkout'),

        ]);
        return redirect()->away($session->url);



        }
    }

    /*public function success(){
        return "Thanks for your order. You have just completed your payment. The seeler will reach out to you as soon as possible";
    }*/

    public function thankyou($id){
        return view('front.thanks',[
            'id'  => $id
        ]);
        
    }
    
    public function getOrderSummery(Request $request) {
        $subTotal = Cart::subtotal(2, '.', '');
        $discount=0;
        $discountString='';

        //Apply discount here 
        if(session()->has('code')){
            $code=session()->get('code');
            if ($code->type =='percent'){
                $discount=($code->discount_amount/100)*$subTotal;

            }else{
                $discount=$code->discount_amount;
            }

            $discountString='<div  class="mt-4" id="discount-response">
            <strong>'.session()->get('code')->code.'</strong>
            <a class="btn btn-sm btn-danger" id="remove-discount"><i class="fa fa-times"></i></a>
        </div>';



        }
        if ($request->country_id > 0) {
          
            $shippingInfo = ShippingCharge::where('country_id', $request->country_id)->first();
    
            $totalQty = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }
    
            if ($shippingInfo != null) {
                $shippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal = ($subTotal-$discount) + $shippingCharge;
    
                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2),
                    'discount'=>number_format($discount,2), 
                    'discountString'=>$discountString,
                    'shippingCharge' => number_format($shippingCharge, 2),
                ]);
            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
    
                if ($shippingInfo != null) {
                    $shippingCharge = $totalQty * $shippingInfo->amount;
                    $grandTotal =($subTotal-$discount) + $shippingCharge;
                } else {
                    // Handle case where 'rest_of_world' also returns null
                    $shippingCharge = 0;
                    $grandTotal = $subTotal;
                }
    
                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2),
                    'discount'=>number_format($discount,2),
                    'discountString'=>$discountString,
                    'shippingCharge' => number_format($shippingCharge, 2),
                ]);
            }
        } else {
            $grandTotal = 0;
            return response()->json([
                'status' => true,
                'grandTotal' => number_format(($subTotal-$discount), 2),
                'discount'=>number_format($discount,2),
                'discountString'=>$discountString,
                'shippingCharge' => number_format(0, 2),
            ]);
        }
    }




    public function applyDiscount(Request $request)
    {

        $code= DiscountCoupon::where('code',$request->code)->first();


        if($code==null){
            return response()->json([
                'status'=>false,
                'message'=>'Invalid discount coupon',
            ]);
        }

        //check if coupon start date is valid or not 
        $now=Carbon::now();
       

        if($code->starts_at !=""){
            $startDate =Carbon::createFromFormat( 'Y-m-d H:i:s', $code->starts_at);

            if($now->lt($startDate)){
                return response()->json([
                    'status'=>false,
                    'message'=>'invalid discount coupon',
                ]);
            }
        }
        

        if($code->expires_at !=""){
            $endDate =Carbon::createFromFormat( 'Y-m-d H:i:s', $code->expires_at);

            if($now->gt($endDate)){
                return response()->json([
                    'status'=>false,
                    'message'=>'invalid discount coupon 2',
                ]);
            }
        }

        //max uses
           if($code->max_uses>0){

                    $couponUsed=Order::where('coupon_code_id',$code->id)->count();
                if($couponUsed>=$code->max_uses){
                    return response()->json([
                        'status'=>false,
                        'message'=>'invalid discount coupon'
                    ]);
                }

           }
        
        //max uses user 
        if($code->max_uses_user>0){
            $couponUsedByUser=Order::where(['coupon_code_id'=>$code->id,'user_id'=>Auth::user()->id])->count();
            if($couponUsedByUser>=$code->max_uses_user){
                return response()->json([
                    'status'=>false,
                    'message'=>'you already used this coupon '
                ]);
            }

        }
        // min amount 
        $subTotal=Cart::subtotal(2, '.', '');

        if( $code->min_amount>0){
            if($subTotal<  $code->min_amount){
                return response()->json([
                    'status'=>false,
                    'message'=>'Your min amount must be' .$code->min_amount.'DT .',
                ]);
            }
        }
       







        session()->put('code',$code);
        return $this->getOrderSummery($request);


    }






    public function removeCoupon(Request $request){
        session()->forget('code');
        return $this->getOrderSummery( $request);
    }


    

}