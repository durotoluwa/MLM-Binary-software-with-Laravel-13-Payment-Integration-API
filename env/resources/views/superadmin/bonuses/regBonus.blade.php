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
            <a href=""> Registration Payment Bonus</a>
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
                     <th>Name</th>
                     <th>username</th>
            <th>Amount</th>
             <th>Account Details</th>
              <th>Decription</th>
         
                    <th>Status</th>
                     
                  
                 
                  </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                      @foreach($bonuses  as $bonus)
                  <tr>
                       <td>{{ $no }}</td>
                       <td>{{ $bonus->user->first_name }} {{ $bonus->user->last_name }}</td>
                        <td>{{ $bonus->user->username }}  </td>
                     <td>&#8358;{{ number_format($bonus->amount, 2) }}</td>

                        <td style="color:green; font-size:13px; font-weight:bold;">{{ $bonus->user->account_name}}<br>{{ $bonus->user->bank_name}}<br> {{ $bonus->user->account_no}}</td>
                  
                       <td>{{ $bonus->description }}</td>
                       
               <td>{{ $bonus->is_paid ? 'Paid' : 'Unpaid' }}</td>

            
       
 
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
