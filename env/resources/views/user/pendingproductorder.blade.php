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
 <!--=====================  Page Title Start Here =====================-->
    <div class="page-titles">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="{{ route('user.dashboard') }}">Dashboard</a>
          </li>
          <li class="breadcrumb-item active">
            <a href="">Pending Product Order</a>
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


<table id="responsiveTable" class="display responsive nowrap w-100">
    <thead>
        <tr>
            <th>#</th>
            <th>Payment Method</th>
            <th>Amount</th>
            <th>Submitted</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pendingproduct as $index => $pkg)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $pkg->payment_method }}</td>
            <td>₦{{ number_format($pkg->amount, 2) }}</td>
            <td>{{ $pkg->created_at->format('Y-m-d H:i') }}</td>
            <td>
                <button class="btn btn-rounded btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#orderModal{{ $pkg->id }}">
                    View Order
                </button>
            </td>
        </tr>

        @endforeach
    </tbody>
</table>

        
        <!-- Modal -->
        <div class="modal fade" id="orderModal{{ $pkg->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Order NO: {{ $pkg->order_no }} </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if($pkg->items->count())
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>APC</th>
                                        <th>CTP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pkg->items as $item)
                                    <tr>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>₦{{ number_format($item->price, 2) }}</td>
                                        <td>{{ $item->apc }}</td>
                                        <td>{{ $item->ctp }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>No products found for this order.</p>
                        @endif

                        <h3>Payment Details</h3>
                        <p><strong>Payment Method:</strong> {{ $pkg->payment_method }}</p>
                        <p><strong>Amount:</strong> ₦{{ number_format($pkg->amount, 2) }}</p>
                        <p><strong>Payment Status:</strong> {{ $pkg->status }}</p>  

                    </div>
                </div>
            </div>
        </div>  

 

</div>
</div></div></div></div><!-----end of row----->
    



  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
