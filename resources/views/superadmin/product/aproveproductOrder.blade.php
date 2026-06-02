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
            <a href="">Aprrove Product Order</a>
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
                    <th>Full Name</th>
                
            
                <th>Payment Method</th>
                <th>Amount</th>
            
                <th>Submitted</th>
                <th>  </th>
               
                 
                  </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                         @foreach($pendingproduct as $pkg)
                  <tr>
                       <td>{{ $no }}</td>
                        <td>{{ $pkg->user->first_name ?? 'N/A' }} {{ $pkg->user->last_name ?? 'N/A' }}</td>
                      
                 
                    <td>{{ $pkg->payment_method }}</td>
                    <td>₦{{ number_format($pkg->amount, 2) }}</td>
                   
                 <td>{{ $pkg->created_at->format('F j, Y \a\t g:i A') }}</td>


             <td>

                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#orderModal{{ $pkg->id }}">
                        View Order
                    </button>
             </td>
               
 
                  </tr>

                  
             
    <?php $no++; ?>
 @endforeach
</tbody></table>
        

{{-- Put all modals OUTSIDE the table --}}
@foreach($pendingproduct as $order)
<div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1"
     aria-labelledby="orderModalLabel{{ $order->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel{{ $order->id }}">
                    Order #{{ $order->order_no }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
  

                <h6>Order Items</h6>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>APC</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ number_format($item->apc, 2) }}</td>
                                <td>{{ number_format($item->apc + $item->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <h2> Total Amount: ₦{{ number_format($order->amount, 2) }}</h2>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach
</div>
</div>


 

</div>
</div></div></div></div><!-----end of row----->
    



  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
