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
            <a href="">Payout History</a>
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
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>S/N</th>
                     <th>Name</th>
                     <th>Amount</th>

                    <th>Status</th>
                    <th>Type</th>
                    <th>date</th>
                
                  </tr>
                </thead>
                <tbody>
                    
                    <?php $no = 1; ?>
                      @foreach($transactions as $data)
                  <tr>
                    <td><input type="checkbox" name="payout_ids[]" value="{{ $data->id }}"></td>
                       <td>{{ $no }}</td>
                <td>{{ $data->user->first_name ?? 'N/A' }} {{ $data->user->last_name ?? 'N/A' }}</td>
                  <td>₦{{ number_format($data->amount, 2) }}
</td>
                        
 
<td>{{ $data->status }}</td>
<td>{{ $data->type }}</td>
                        
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
