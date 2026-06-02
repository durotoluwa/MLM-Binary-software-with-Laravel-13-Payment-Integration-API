@extends('layouts.app')
@section('content')


@include('layouts.navbar')
 
 

        <!--******** Header start **********-->
   @include('superadmin.headertop')      
 
        <!--******* Header end *************-->

        <!--******* Sidebar start **********-->
     
@include('superadmin.sidebar')    

        <!--******** Sidebar end ***********-->

        <!-- Container starts-->
        <div class="content-body">
  <!-- row -->
  <div class="container-fluid">
   <div class="page-titles">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('user.dashboard') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">
          <a href="">Product List</a>
        </li>
      </ol>
    </div>

 

@php
    $latestPackage = auth()->user()
        ->userPackages()
        ->latest('id')
        ->first();
@endphp

@if($latestPackage && $latestPackage->package_order_status === 'pending')
    <div class="alert alert-warning text-center" style="margin:0; border-radius:0;">
        You have a pending package that requires product selection.
        <a class="btn btn-sm btn-primary ms-2" 
           href="{{ route('user.package-products', ['package_id' => $latestPackage->package_id]) }}">
            Click here to select products
        </a>
    </div>
@endif

@if($pendingProductOrder)
    <div class="alert alert-warning text-center" style="margin:0; border-radius:0;">
        You have a pending product order.
        <a class="btn btn-sm btn-primary ms-2" 
           href="{{ route('user.pendingproductorder') }}">
            Click here to view
        </a>
    </div>
@endif

@if (auth()->user()->isImpersonated())
    <a href="{{ route('impersonate.leave') }}" class="btn btn-danger">
        Back to Admin
    </a>
@endif

     <script>
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @elseif(session('error'))
            toastr.error("{{ session('error') }}");
        @endif
    </script>

@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

    <div class="row">


  @foreach ($productlist as $data)
        @php
            $cart = session('cart', []);
            $inCart = isset($cart[$data->id]);
        @endphp

        <div class="col-lg-12 col-xl-6 col-xxl-4">
            <div class="card">
                <div class="card-body">
                    <div class="row m-b-30">
                        <div class="col-md-5 col-xxl-12">
                            <div class="new-arrival-product mb-4 mb-xxl-4 mb-md-0">
                                <div class="new-arrivals-img-contnent">
                                    <img src="{{ asset($data->product_image) }}" class="img-fluid" alt="{{ $data->productName }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 col-xxl-12">
                            <div class="new-arrival-content position-relative">
                                <h4>
                                    <a href="#">{{ $data->productName }}</a>
                                </h4>
                                <div class="comment-review star-rating">
                                    <p class="price">₦{{ number_format($data->price, 2) }}</p>
                                </div>
                                <p>CPT: <span class="item">{{ $data->cpts }}</span></p>
                                <p>APC: <span class="item">₦{{ number_format($data->apc, 2) }}</span></p>
                                <p class="text-content">
                                    {{ $data->description }}
                                </p>

                     <!-- Add to Cart / Already in Cart -->
@if($inCart)
    <button class="btn btn-secondary w-100" disabled>
        Added to Cart <span class="btn-icon-end"><i class="fa fa-check"></i></span>
    </button>
@else
    <form action="{{ route('cart.add', $data->id) }}" method="POST" class="d-flex flex-wrap gap-2 align-items-center">
        @csrf
        <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm" style="width: 80px;" required>
        <button type="submit" class="btn btn-primary">
            Add to Cart <span class="btn-icon-end"><i class="fa fa-shopping-cart"></i></span>
        </button>
    </form>
@endif


                         
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </div>
    <!--========end row==========-->

 
  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')


<script>
    // Remove single item from cart
    document.querySelectorAll('.btn-remove-cart').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;

            fetch(`/cart/remove/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => location.reload());
        });
    });

    // Clear all items from cart
    document.querySelector('.btn-clear-cart')?.addEventListener('click', function () {
        if (confirm('Are you sure you want to clear the entire cart?')) {
            fetch(`/cart/clear`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => location.reload());
        }
    });
</script>


@endsection
