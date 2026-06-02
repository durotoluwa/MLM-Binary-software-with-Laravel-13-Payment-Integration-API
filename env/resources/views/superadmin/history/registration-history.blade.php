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
            <a href="">Registration Payment History</a>
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
                     <th>Username</th>
                    <th>Full Name</th>
                    <th>Payment Method</th>
                    
                 
                    <th>Status</th>
                    <th>Date</th>
                
                  </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                      @foreach($transactions as $data)
                  <tr>
                       <td>{{ $no }}</td>
                <td>{{ $data->user->username ?? 'N/A' }}</td>
<td>{{ $data->user->first_name ?? 'N/A' }} {{ $data->user->last_name ?? 'N/A' }}</td>
                        <td>{{ $data->method }}</td>
 
 
              <td>
                            @if($data->status == 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($data->status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @else
                                <span class="badge badge-danger">Declined</span>
                            @endif
        
                        </td>

                 

                         <td>{{ $data->created_at->format('F j, Y \a\t g:i A') }}</td>
                <!----   <td>


                           
<button data-bs-toggle="modal" data-bs-target="#basicModal{{ $data->id }}"  type="button" class="btn btn-rounded btn-outline-danger"><i class="fa-solid fa-trash"></i> Delete</button>     


                    </td>--->
    


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
