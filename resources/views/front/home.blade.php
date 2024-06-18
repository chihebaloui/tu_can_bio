@extends('front.layouts.app')

@section('content')

<section class="section-1 w-100 h-100 d-flex flex-column justify-content-center align-items-center">
    <section class="section-1 w-25 h-100 d-flex flex-column justify-content-center align-items-center">

        
        <div id="carouselExampleIndicators" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="false">
            
            <div class="carousel-inner ">
            
                <div class="carousel-item active "  >
                    <!-- <img src="images/carousel-1.jpg" class="d-block w-100" alt=""> -->

                    <img src="{{asset('front-assets/design/1.png')}}" alt=""  />

                    <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                        <div class="p-3">
                            <a class="btn btn-outline-light py-2 px-4 mt-3" href="{{route('front.shop')}}">Shop Now</a>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    
                    
                    <img src="{{asset('front-assets/design/2.png')}}" alt="" />

                    <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                        <div class="p-3">
                            <a class="btn btn-outline-light py-2 px-4 mt-3" href="#">Shop Now</a>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <!-- <img src="images/carousel-3.jpg" class="d-block w-100" alt=""> -->

                    <img src="{{asset('front-assets/design/3.png')}}" alt=""  />


                    <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                        <div class="p-3">
                            <a class="btn btn-outline-light py-2 px-4 mt-3" href="#">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </section>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</section>
    <section class="section-2">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="box shadow-lg">
                        <div class="fa icon fa-check text-primary m-0 mr-3"></div>
                        <h2 class="font-weight-semi-bold m-0">Quality Product</h5>
                    </div>                    
                </div>
                <div class="col-lg-3 ">
                    <div class="box shadow-lg">
                        <div class="fa icon fa-shipping-fast text-primary m-0 mr-3"></div>
                        <h2 class="font-weight-semi-bold m-0">Free Shipping</h2>
                    </div>                    
                </div>
                <div class="col-lg-3">
                    <div class="box shadow-lg">
                        <div class="fa icon fa-exchange-alt text-primary m-0 mr-3"></div>
                        <h2 class="font-weight-semi-bold m-0">14-Day Return</h2>
                    </div>                    
                </div>
                <div class="col-lg-3 ">
                    <div class="box shadow-lg">
                        <div class="fa icon fa-phone-volume text-primary m-0 mr-3"></div>
                        <h2 class="font-weight-semi-bold m-0"> Support</h5>
                    </div>                    
                </div>
            </div>
        </div>
    </section>
    <section class="section-3">
        <div class="container">
            <div class="section-title">
                <h2>Categories</h2>
            </div>           
            <div class="row pb-3">
                @if(getCategories()->isNotEmpty())
                @foreach(getCategories() as $category)
                <div class="col-lg-3">
                    <div class="cat-card">
                        <div class="left">
                            @if($category->image!="")
                            <img src="{{asset('uploads/category/thumb/'.$category->image)}}" alt="" class="img-fluid">
                            @endif
                        </div>
                        <div class="right">
                            <div class="cat-data">
                                <h2>{{$category->name}}</h2>
                                {{--<p>100 Products</p>--}}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @endif


            </div>
        </div>
        
    </section>


<section class="section-4 pt-5">
    <div class="container">
        <div class="section-title">
            <h2>Featured Products</h2>
        </div>    
        <div class="row pb-3">
            @if($featuredProducts->isNotEmpty())
            @foreach($featuredProducts as $product)
        
                @php 
                    $productImage = $product->product_images->first();
                @endphp
        
                <div class="col-md-3">
                    <div class="card product-card">
                        <div class="product-image position-relative">
                            <a href="{{route("front.product",$product->slug)}}" class="product-img">
                                @if(!empty($productImage->image))
                                    <img class="card-img-top" src="{{ asset('uploads/product/small/' . $productImage->image) }}" alt="" class="img-thumbnail"/>
                                @else 
                                    <img class="card-img-top img-thumbnail" src="{{ asset('admin-assets/img/default-150x150.png') }}" alt="">
                                @endif
                            </a>
                            <a onclick ="addToWishlist({{$product->id}})"class="whishlist" href="javascript:void(0);"><i class="far fa-heart"></i></a>                            
        
                            <div class="product-action">
                                @if($product->track_qty == 'Yes')
                                    @if($product->qty > 0)
                                        <a class="btn btn-dark" href="javascript:void(0);" onclick="addToCart({{ $product->id }});">
                                            <i class="fa fa-shopping-cart"></i> Add To Cart
                                        </a>
                                    @else
                                        <a class="btn btn-dark" href="javascript:void(0);">
                                            Out of stock
                                        </a>
                                    @endif
                                @else
                                    <a class="btn btn-dark" href="javascript:void(0);" onclick="addToCart({{ $product->id }});">
                                        <i class="fa fa-shopping-cart"></i> Add To Cart
                                    </a>
                                @endif
                            </div>
                            
                        </div>                        
                        <div class="card-body text-center mt-3">
                            <a class="h6 link" href="product.php">{{$product->title}}</a>
                            <div class="price mt-2">
                                <span class="h5"><strong>{{$product->price}} DT</strong></span>
                                @if($product->compare_price > 0)
                                    <span class="h6 text-underline"><del>{{$product->compare_price}} DT</del></span>
                                @endif
                            </div>
                        </div>                        
                    </div>                                               
                </div>  
            @endforeach
        @endif
        
        </div>
    </div>
</section>

<section class="section-4 pt-5">
    <div class="container">
        <div class="section-title">
            <h2>Latest Produsts</h2>
        </div>    
        <div class="row pb-3">
            @if($latestProducts->isNotEmpty())
                @foreach($latestProducts as $product)
                    @php 
                        $productImage = $product->product_images->first();
                    @endphp
                    <div class="col-md-3">
                        <div class="card product-card">
                            <div class="product-image position-relative">
                                <a href="{{route("front.product",$product->slug)}}" class="product-img">
                                    @if(!empty($productImage->image))
                                        <img class="card-img-top" src="{{ asset('uploads/product/small/' . $productImage->image) }}" alt="" class="img-thumbnail"/>
                                    @else 
                                        <img class="card-img-top img-thumbnail" src="{{ asset('admin-assets/img/default-150x150.png') }}" alt="">
                                    @endif
                                </a>
                                <a onclick ="addToWishlist({{$product->id}})"class="whishlist" href="javascript:void(0);"><i class="far fa-heart"></i></a>                 
            
                                <div class="product-action">
                                    @if($product->track_qty == 'Yes')
                                        @if($product->qty > 0)
                                            <a class="btn btn-dark" href="javascript:void(0);" onclick="addToCart({{ $product->id }});">
                                                <i class="fa fa-shopping-cart"></i> Add To Cart
                                            </a>
                                        @else
                                            <a class="btn btn-dark" href="javascript:void(0);">
                                                Out of stock
                                            </a>
                                        @endif
                                    @else
                                        <a class="btn btn-dark" href="javascript:void(0);" onclick="addToCart({{ $product->id }});">
                                            <i class="fa fa-shopping-cart"></i> Add To Cart
                                        </a>
                                    @endif
                                </div>
                                
                            </div>                        
                            <div class="card-body text-center mt-3">
                                <a class="h6 link" href="product.php">{{$product->title}}</a>
                                <div class="price mt-2">
                                    <span class="h5"><strong>{{$product->price}} DT</strong></span>
                                    @if($product->compare_price > 0)
                                        <span class="h6 text-underline"><del>{{$product->compare_price}} DT</del></span>
                                    @endif
                                </div>
                            </div>                        
                        </div>                                               
                    </div>  
                @endforeach
            @endif
        </div>
        
        
</section>
@endsection
@section('customJs')
<script type="text/javascript">
function addToCart(id){
    $.ajax({
        url:'{{route("front.addToCart")}}',
        type:'post',
        data:{id:id},
        dataType:'json',
        success:function(response){
            if(response.status==true){
                window.location.href="{{route('front.cart')}}";
            } else{
                alert(response.message);
            }

        }
    });
}

</script>
@endsection

