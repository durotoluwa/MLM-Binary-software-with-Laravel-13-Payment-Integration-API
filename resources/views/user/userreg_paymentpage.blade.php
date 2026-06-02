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
   <div class="page-titles">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('user.dashboard') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">
          <a href="">Pending User Registration Payment</a>
        </li>
      </ol>
    </div>


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
<div class="card">
<div class="card-body">
  
   @if($pendingUsers->isEmpty())
        <div class="alert alert-info">No pending registrations found.</div>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>UserReg ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Payment Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingUsers as $user)
                    <tr>
                        <td>{{ $user->userreg_id }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ ucfirst($user->status) }}</td>
                        <td>{{ ucfirst($user->payment_status) }}</td>
                        <td>
                            <a href="{{ route('user.userreg_pay', $user->id) }}" class="btn btn-success btn-sm">Pay Now</a>
                            <form action="{{ route('user.delete', $user->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this registration?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

 
  </div>
</div> </div> </div>

        <!-- Container Ends-->

@include('layouts.footer_content')

 

@endsection
