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


<div class="table-responsive">
        
         <table id="responsiveTable" class="display responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Full Name</th>
                
            
                <th>Payment Method</th>
                <th>Amount</th>
                <th>Proof</th>
                <th>Submitted</th>
                <th>  </th>
                <th>Action</th>
                 
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
                    <td>
                        @if($pkg->proof)
                            <a href="{{ asset($pkg->proof) }}" target="_blank">View Proof</a>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $pkg->created_at->format('Y-m-d H:i') }}</td>

             <td>

                 <button class="btn btn-rounded btn-outline-primary" >View Order</button>
             </td>
                 <td>
                <form action="{{ route('superadmin.orders.approve', $pkg->id) }}" method="POST" onsubmit="return confirm('Approve this product order?')">
    @csrf
    <button class="btn btn-rounded btn-outline-primary">Approve</button>
</form>

                        
                </td>
       
 
                  </tr>

                  
             
    <?php $no++; ?>
 @endforeach
</tbody></table>
          

 

</div>
</div></div></div></div><!-----end of row----->
    



  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
