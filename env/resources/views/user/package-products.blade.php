@extends('layouts.app')
@section('content')


@include('layouts.navbar')
 

        <!--******** Header start **********-->
   @include('user.headertop')      
 
        <!--******* Header end *************-->

        <!--******* Sidebar start **********-->
     
@include('user.sidebar')     

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
          <a href="">Package Product Order</a>
        </li>
      </ol>
    </div>
 <div class="row">
    
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

 <div class="col-xl-12">
<div class="card">
<div class="card-body">
<div class="row separate-row">
                  <div class="col-sm-6">
                    <div class="job-icon pb-4 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h2 class="mb-0 lh-1">{{ $package->packageName }}</h2>
                         
                        </div>
                        <span class="d-block mb-2">Package Name</span>
                      </div>
                     
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="job-icon pb-4 pt-4 pt-sm-0 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h2 class="mb-0 lh-1">₦{{ number_format($package->price, 2) }}</h2>
                        </div>
                        <span class="d-block mb-2">Package  Price</span>
                      </div>
                   
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="job-icon pt-4 pb-sm-0 pb-4 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h2 class="mb-0 lh-1">{{ $package->bottle }}</h2>
                         
                        </div>
                        <span class="d-block mb-2">Max Bottle</span>
                      </div>
                   
                    </div>
                  </div>


                        <div class="col-sm-6">
                    <div class="job-icon pt-4 pb-sm-0 pb-4 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h2 class="mb-0 lh-1">{{ $package->cpts }}</h2>
                         
                        </div>
                        <span class="d-block mb-2">Package CPT</span>
                      </div>
                   
                    </div>
                  </div>
                 













</div></div>
 </div><!--========end col==========-->
    </div><!--========end row==========-->


  

 <div class="row">
 <div class="col-xl-12">
<div class="card">
<div class="card-body">

   <form action="{{ route('user.package.products.save', $package->id) }}" method="POST">
        @csrf
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                       <th>Product Image</th>
                         <th>Product CPT</th>
                    <th>Price</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>
                        {{ $product->productName }}
                        <input type="hidden" name="product[{{ $loop->index }}][id]" value="{{ $product->id }}">
                    </td>
                    <td>
                        @if($product->product_image)
                            <img src="{{ asset($product->product_image) }}" alt="{{ $product->productName }}" style="width: 50px; height: 50px;">
                        @else
                            <span>No Image</span>
                        @endif
                    </td>
                       <td>
                        {{ $product->cpts }}</td>
                    <td>₦{{ number_format($product->price, 2) }}</td>
                  
                    <td>
                        <input type="number" name="product[{{ $loop->index }}][qty]" class="form-control" value="0" min="0">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Confirm Selection</button>
    </form>



</div></div>
 </div></div>



 
  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')

<script>
document.addEventListener('DOMContentLoaded', function () {
  const select = document.getElementById('payment-method-select');
  const walletPin = document.getElementById('wallet-pin-group');

  const bankFields = ['bankName', 'acctName', 'Amount', 'proof-upload'];

  function handlePaymentChange() {
    const selected = select.value;

    // Show/hide wallet pin input
    walletPin.style.display = (selected === 'wallet') ? 'block' : 'none';

    // Show/hide bank transfer fields
    bankFields.forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.style.display = (selected === 'bank') ? 'block' : 'none';
      }
    });
  }

  select.addEventListener('change', handlePaymentChange);
  handlePaymentChange(); // Initial trigger
});
</script>



@endsection
