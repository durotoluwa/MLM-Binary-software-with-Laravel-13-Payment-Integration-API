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
 <!--=====================  Page Title Start Here =====================-->
    <div class="page-titles">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="{{ route('user.dashboard') }}">Dashboard</a>
          </li>
          <li class="breadcrumb-item active">
            <a href="">View Package Product Details</a>
          </li>
        </ol>


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
<div class="col-xl-12">
<div class="card h-auto">
<div class="card-body">
 <h4>Package Order – {{ $package->package->packageName ?? '' }}</h4>
@if($productOrders->count())
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Product Image</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productOrders as $order)
                        <tr>
                            <td>{{ $order->product->productName ?? 'N/A' }}</td>
                            <td><img src="{{ asset($order->product->product_image) }}" width="30" height="30"></td>
                            <td>{{ $order->qty }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No products have been selected for this package.</p>
        @endif

        <br> 

        <h5 class="mt-4">Package Details</h5>
      
<table class="table table-bordered">
  <thead>
    <tr>
      <th scope="col">Amount Paid</th>
      <th scope="col">Payment Method</th>
      <th scope="col">Approved Date</th>
      <th scope="col">Status</th>
    </tr>
  </thead>


  <tbody>
    <tr>
      <th>₦{{ number_format($package->amount_paid, 2) }}</th>
      <td>{{ $package->payment_method }}</td>
      <td>{{ \Carbon\Carbon::parse($package->approved_at)->format('F j, Y \a\t h:i A') }}</td>
      <td>   @if($package->status === 'approved')
                <span class="badge bg-success">Approved</span>
            @else
                <span class="badge bg-danger">Pending</span>
            @endif
        </td>
    </tr>
  
  </tbody>
</table><br> 



 <h5 class="mt-4">Package Transaction</h5>
      
<table class="table table-bordered">
    <thead>
        <tr>
            <th scope="col">Name</th>
            <th scope="col">Bonus Type</th>
            <th scope="col">Amount</th>
        </tr>
    </thead>

    <tbody>
        @forelse($bonusHistory as $bonus)
            <tr>
                <td>{{ $bonus->receiver->username ?? 'Unknown' }}</td>
                <td>{{ ucfirst($bonus->type) }}</td>
                <td>₦{{ number_format($bonus->amount, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center">No transaction history found.</td>
            </tr>
        @endforelse
    </tbody>
</table>







        <h5 class="mt-4">Delivery Address</h5>
        <p><strong>Address:</strong> {{ $package->user->address ?? 'N/A' }}</p>
        <p><strong>City:</strong> {{ $package->user->city ?? 'N/A' }}</p>
        <p><strong>State:</strong> {{ $package->user->state ?? 'N/A' }}</p>
        <p><strong>Country:</strong> {{ $package->user->country ?? 'N/A' }}</p>


</div></div></div></div><!-----end of row----->
    



  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')

<script>
function printModal(elementId) {
    var printContents = document.getElementById(elementId).innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // Optional: reload to restore modal functionality
}
</script>


@endsection
