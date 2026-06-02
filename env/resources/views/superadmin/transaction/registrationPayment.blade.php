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
            <a href=""> Registration Payment</a>
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
                    <th>S/N</th>
                     <th>Full Name</th>
                     <th>User</th>
                    <th>Payment Method</th>
         
                    <th>Status</th>
                    <th>Submitted At</th>
                 
                  </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                      @foreach($transactions as $data)
                  <tr>
                       <td>{{ $no }}</td>
                       <td>{{ $data->user->first_name }} {{ $data->user->last_name }}</td>
                <td>{{ $data->user->username ?? 'N/A' }}</td>
                        <td>{{ $data->method }}</td>
                       
              <td><a  class="badge badge-rounded badge-secondary">{{ $data->status }}</a> </td>

                        <td>{{ $data->created_at->format('F j, Y \a\t g:i A') }}</td>
       
 
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
