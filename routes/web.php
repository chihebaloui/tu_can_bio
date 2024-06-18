<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\DiscountCodeController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\productImageController;
use App\Http\Controllers\admin\ProductSubCategoryController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\TempImagesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StripeController;
use App\Models\DiscountCoupon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Voici où vous pouvez enregistrer les routes web pour votre application. 
| Ces routes sont chargées par le RouteServiceProvider et toutes seront 
| assignées au groupe de middleware "web". Faites quelque chose de génial !
|
*/
/*
Route::get('/', function () {
    // Retourne la vue de bienvenue
    return view('welcome');
});*/

Route::get('/test',function(){
  orderEmail(18);
});



  // route front : 
Route::get('/',[FrontController::class,'index'])->name('front.home');
Route::get('/shop/{categorySlug?}/{subCategorySlug?}',[ShopController::class,'index'])->name('front.shop');
Route::get('/product/{slug}',[ShopController::class,'product'])->name('front.product');
   //route of cart 
Route::get('/cart',[CartController::class,'cart'])->name('front.cart');
Route::post('/add-to-cart',[CartController::class,'addToCart'])->name('front.addToCart');
Route::post('/update-cart',[CartController::class,'updateCart'])->name('front.updateCart');
Route::post('/delete-item',[CartController::class,'deleteItem'])->name('front.deleteItem.cart');
Route::get('/checkout',[CartController::class,'checkout'])->name('front.checkout');
Route::post('/get-order-summery',[CartController::class,'getOrderSummery'])->name('front.getOrderSummery');
//applyDiscount 
Route::post('/apply-discount',[CartController::class,'applyDiscount'])->name('front.applyDiscount');
Route::post('/remove-discount',[CartController::class,'removeCoupon'])->name('front.removeCoupon');
Route::post('/add-to-wishlist',[FrontController::class,'addToWishlist'])->name('front.addToWishlist');
// ratings 
Route::post('/save-rating/{productId}',[ShopController::class,'saveRating'])->name('front.saveRating');




Route::post('/process-checkout',[CartController::class,'processCheckout'])->name('front.processCheckout');
//Route::get('/process-checkout',[CartController::class,'processCheckout'])->name('front.processCheckout');

   
Route::get('/checkout',[CartController::class,'checkout'])->name('front.checkout');
Route::post('/session',[CartController::class,'session'])->name('front.session');
Route::get('/success/{orderId}',[CartController::class,'success'])->name('front.success');


// THANKS ROUTE
Route::get('/thanks/{orderId}',[CartController::class,'thankyou'])->name('front.thankyou');


                //login client 
    
    Route::group(['prefix'=>'account'],function(){

        Route::group(['middleware'=>'guest'],function(){

            Route::get('/login',[AuthController::class,'login'])->name('account.login');
            Route::post('/login',[AuthController::class,'loginPost'])->name('account.authenticate');

             //login /tegister route 
            Route::get('/register',[AuthController::class,'register'])->name('account.register');
            Route::post('/register',[AuthController::class,'registerPost'])->name('account.register');

            

        });

        Route::group(['middleware'=>'auth'],function(){
            Route::get('/profile',[AuthController::class,'profile'])->name('account.profile');
            Route::get('/my-orders',[AuthController::class,'orders'])->name('account.orders');
            Route::get('/order-detail/{orderId}',[AuthController::class,'orderDetail'])->name('account.orderDetail');
            Route::get('/my-wishlist',[AuthController::class,'wishlist'])->name('account.wishlist');
            Route::post('/remove-product-from-wishlist',[AuthController::class,'removeProductFromWishlist'])->name('account.removeProductFromWishlist');
            Route::get('/logout',[AuthController::class,'logout'])->name('account.logout');
            


           

            
        });









    });






Route::group(['prefix'=>'admin'],function(){

    // Groupe de routes pour l'administration

    Route::group(['middleware'=>'admin.guest'],function(){

        // Routes accessibles uniquement pour les invités (non connectés)

        Route::get('/login',[AdminLoginController::class,'index'])->name('admin.login');
        Route::post('/authenticate',[AdminLoginController::class,'authenticate'])->name('admin.authenticate');

    });

    Route::group(['middleware'=>'admin.auth'],function(){
        
        // Routes accessibles uniquement pour les utilisateurs authentifiés en tant qu'administrateurs

        Route::get('/dashboard',[HomeController::class,'index'])->name('admin.dashboard');
        Route::get('/logout',[HomeController::class,'logout'])->name('admin.logout');




        // category routes
        Route::get('/categories/create',[CategoryController::class,'create'])->name('categories.create');
        Route::post('/categories',[CategoryController::class,'store'])->name('categories.store');
        Route::get('/categories',[CategoryController::class,'index'])->name('categories.index');
        //edit
        Route::get('/categories/{category}/edit',[CategoryController::class,'edit'])->name('categories.edit');
        //update
        Route::put('/categories/{category}',[CategoryController::class,'update'])->name('categories.update');
        //Delete category
        Route::delete('/categories/{category}',[CategoryController::class,'destroy'])->name('categories.delete');

        //tem-images.create
        Route::post('/upload-temp-image',[TempImagesController::class,'create'])->name('temp-images.create');





        //sub_category routes 
        Route::get('/sub-categories',[SubCategoryController::class,'index'])->name('sub-categories.index');
             //crate routes
        Route::get('/sub-categories/create',[SubCategoryController::class,'create'])->name('sub-categories.create');
        Route::post('/sub-categories',[SubCategoryController::class,'store'])->name('sub-categories.store');
           //edit route
        Route::get('/sub-categories/{subCategory}/edit',[SubCategoryController::class,'edit'])->name('sub-categories.edit');
         //update routes
        Route::put('/sub-categories/{subCategory}',[SubCategoryController::class,'update'])->name('sub-categories.update');
        //delete routes
        Route::delete('/sub-categories/{subCategory}',[SubCategoryController::class,'destroy'])->name('sub-categories.delete');



        //brands route
        Route::get('/brands',[BrandController::class,'index'])->name('brands.index');
        Route::get('/brands/create',[BrandController::class,'create'])->name('brands.create');
        Route::post('/brands',[BrandController::class,'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit',[BrandController::class,'edit'])->name('brands.edit');
        Route::put('/brands/{brand}',[BrandController::class,'update'])->name('brands.update');
        Route::delete('/brands/{brand}',[BrandController::class,'destroy'])->name('brands.delete');


        //shipping route 
        Route::get('/shiping/create',[ShippingController::class,'create'])->name('shipping.create');
        Route::post('/shiping',[ShippingController::class,'store'])->name('shipping.store');
        Route::get('/shiping/{id}',[ShippingController::class,'edit'])->name('shipping.edit');
        Route::put('/shiping/{id}',[ShippingController::class,'update'])->name('shipping.update');
        Route::delete('/shiping/{id}',[ShippingController::class,'destroy'])->name('shipping.delete');


        //coupon route : 
        Route::get('/coupons',[DiscountCodeController::class,'index'])->name('coupons.index');
        Route::get('/coupons/create',[DiscountCodeController::class,'create'])->name('coupons.create');
        Route::post('/coupons',[DiscountCodeController::class,'store'])->name('coupons.store');
        Route::get('/coupons/{coupon}/edit',[DiscountCodeController::class,'edit'])->name('coupons.edit');
        Route::put('/coupons/{coupon}',[DiscountCodeController::class,'update'])->name('coupons.update');
        Route::delete('/coupons/{coupon}',[DiscountCodeController::class,'destroy'])->name('coupons.delete');

        //Order route 
        Route::get('/orders',[OrderController::class,'index'])->name('orders.index');
        Route::get('/orders/{id}',[OrderController::class,'detail'])->name('orders.detail');
        Route::post('/order/change-status/{id}',[OrderController::class,'changeOrderStatus'])->name('orders.changeOrderStatus');
        Route::post('/order/send-email/{id}',[OrderController::class,'sendInvoiceEmail'])->name('orders.sendInvoiceEmail');








        //Product routes
        Route::get('/products',[ProductController::class,'index'])->name('products.index');
        Route::get('/products/create',[ProductController::class,'create'])->name('products.create');
        Route::post('/products',[ProductController::class,'store'])->name('products.store');
        Route::get('/products/{product}/edit',[ProductController::class,'edit'])->name('products.edit');
        Route::put('/products/{product}',[ProductController::class,'update'])->name('products.update');
        Route::delete('/products/{product}',[ProductController::class,'destroy'])->name('products.delete');
        Route::get('/get-products',[ProductController::class,'getProducts'])->name('products.getProducts');
        Route::get('/ratings',[ProductController::class,'productRatings'])->name('products.productRatings');
        Route::get('/change-rating-status',[ProductController::class,'changeRatingStatus'])->name('products.changeRatingStatus');
        



           //delete images_updates for product
        Route::post('/product-images/update', [productImageController::class, 'update'])->name('product-images.update');
        Route::delete('/product-images', [productImageController::class, 'destroy'])->name('product-images.destroy');




        Route::get('/product-subcategories',[ProductSubCategoryController::class,'index'])->name('product-subcategorie.index');









        //update






        Route::get('/getSlug',function(Request $request){
            $slug='';
            if(! empty($request->title )){
                $slug=Str :: slug($request->title);


            }
            return response()->json ([
                'status'=>true,
                'slug'=>$slug
            ]);
        })->name('getSlug');


    });


    


});
