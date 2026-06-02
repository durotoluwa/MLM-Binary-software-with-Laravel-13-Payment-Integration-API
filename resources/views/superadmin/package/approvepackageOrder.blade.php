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
            <a href="">Approved Package Order</a>
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


<div class="table-responsive">
        
<table id="responsiveTable" class="display responsive nowrap w-100">
    <thead>
        <tr>
            <th>#</th>
            <th>User</th>
            <th>Package</th>
            <th>Payment</th>
            <th>Amount</th>
            <th>Approved Date</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($approvePackages as $pkg)
            <tr>
                <td>{{ $no }}</td>
                <td>{{ $pkg->user->username ?? 'N/A' }}</td>
                <td>{{ $pkg->package->packageName ?? '' }}</td>
                <td>{{ $pkg->payment_method }}</td>
                <td>₦{{ number_format($pkg->amount_paid, 2) }}</td>
                <td>{{ $pkg->updated_at->format('F j, Y \a\t h:i A') }}</td>
<td>
    <a href="{{ route('superadmin.package.order.details', $pkg->id) }}" 
       class="btn btn-rounded btn-outline-primary">
        View Order
    </a>
</td>



            </tr>

 




            @php $no++; @endphp
        @endforeach
    </tbody>
</table>

<!-- Place all modals here after table -->

@foreach($approvePackages as $pkg)
    <div class="modal fade" id="viewOrderModal{{ $pkg->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Package Order – {{ $pkg->package->packageName ?? '' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="printArea{{ $pkg->id }}">
                    @if($pkg->packageProductOrders->count())
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Product Image</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pkg->packageProductOrders as $order)
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
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="printModal('printArea{{ $pkg->id }}')">🖨 Print</button>
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach



          

 

</div>
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
