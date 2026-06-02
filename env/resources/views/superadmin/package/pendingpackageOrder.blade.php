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
            <a href="">Pending Package Order</a>
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
                <th>Payment Method</th>
                  <th>Package price</th>
                <th>Payment</th>
                <th>Proof</th>
                <th>Submitted</th>
                <th>Action</th>
                 
                  </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                         @foreach($pendingPackages as $pkg)
                  <tr>
                       <td>{{ $no }}</td>
                        <td>{{ $pkg->user->username ?? 'N/A' }}</td>
                    <td>{{ $pkg->package->packageName ?? '' }}</td>
                    <td>{{ $pkg->payment_method }}</td>
                           <td>₦{{ $pkg->total_amount_package }}</td>
                    <td>₦{{ number_format($pkg->amount_paid, 2) }}</td>
                    <td>
                        @if($pkg->payment_proof)
                            <a href="{{ asset($pkg->payment_proof) }}" target="_blank">View Proof</a>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $pkg->created_at->format('Y-m-d H:i') }}</td>

             <td>
                    @if(!$pkg->is_approved)
                  
                            

                            <form action="{{ route('superadmin.package.approveorddrpackage', $pkg->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-rounded btn-outline-primary" onclick="return confirm('Approve this package?')">Approve</button>
                        </form>
                        
                    @else
                       Bonus Paid on: {{ $pkg->approved_at->format('F j, Y \a\t g:i A') }}

                    @endif
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
