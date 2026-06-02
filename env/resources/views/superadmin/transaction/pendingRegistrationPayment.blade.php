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
            <a href="">Pending Registration Payment</a>
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
       <table class="table table-responsive-md">
                <thead>
                  <tr>
                    <th>S/N</th>
                     <th>User</th>
                    <th>Sender Name</th>
                    <th>Bank Name</th>
                    <th>Transaction No.</th>
                    <th>Proof</th>
                    <th>Submitted At</th>
                    <th> Action</th>
                  </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                      @foreach($transactions as $data)
                  <tr>
                       <td>{{ $no }}</td>
                <td>{{ $data->user->username ?? 'N/A' }}</td>
                        <td>{{ $data->sendername }}</td>
                        <td>{{ $data->bank_name }}</td>
                        <td>{{ $data->transaction_no }}</td>
              <td>
                            @if($data->proof)
             <a href="{{ asset($data->proof) }}" target="_blank">View Proof</a>
                            @else
                                No Proof
                            @endif
                        </td>

                 

                         <td>{{ $data->created_at->format('F j, Y \a\t g:i A') }}</td>
                    <td>

   <form action="{{ route('superadmin.transaction.approve', $data->id) }}" method="POST" onsubmit="return confirm('Approve this payment?');">
                                @csrf
                                <button type="submit"  class="btn btn-rounded btn-outline-primary">Approve</button>
                            </form>
                           
<button data-bs-toggle="modal" data-bs-target="#basicModal{{ $data->id }}"  type="button" class="btn btn-rounded btn-outline-danger"><i class="fa-solid fa-trash"></i> Delete</button>        

    


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
