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
            <a href="">Approved Incentive List</a>
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
                    <th>#</th>
                      <th>Name</th>
                    <th>User</th>
                    <th>Rank</th>
                    <th>Status</th>
                    <th>Achieved At</th>
                </tr>
                </thead>
                <tbody>
              @foreach($incentives as $incentive)
                <tr>
                    <td>{{ $incentive->id }}</td>
                    <td>{{ $incentive->first_name }} {{ $incentive->last_name }}</td>
                    <td>{{ $incentive->username }}</td>
                    <td>{{ $incentive->rank }}</td>
                    <td>{{ ucfirst($incentive->status) }}</td>
<td>
    {{ $incentive->achieved_at ? \Carbon\Carbon::parse($incentive->achieved_at)->format('d M Y H:i') : '-' }}
</td>

                </tr>
                @endforeach
</tbody></table>
          

 

</div>
</div></div></div></div><!-----end of row----->
    



  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
