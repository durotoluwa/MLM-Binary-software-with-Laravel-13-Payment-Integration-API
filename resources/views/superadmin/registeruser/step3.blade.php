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
            <a href=""> Add Members</a>
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

 <div class="card-header text-center">
                        <h4 class="card-title mt-3">Step 3: Next Of Kin</h4>
                    </div>
<div class="container py-5">

       
 @if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
<form method="POST" action="{{ route('superadmin.registeruser.post', 3) }}">
    @csrf

<div class="row">
      <div class="col-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label ">Full Name</label>
          <input type="text" name="kin_name" class="form-control" value="{{ old('kin_name', $data['kin_name'] ?? '') }}">
        </div>
      </div>
      <div class="col-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label ">Phone Number</label>
          <input type="text" name="kin_phone" class="form-control" value="{{ old('kin_phone', $data['kin_phone'] ?? '') }}">
        </div>
      </div>
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label ">Email</label>
          <input type="email" name="kin_email" class="form-control" value="{{ old('kin_email', $data['kin_email'] ?? '') }}">
        </div>
      </div>
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label ">Address</label>
          <input type="text" name="kin_address" class="form-control" value="{{ old('kin_address', $data['kin_address'] ?? '') }}">
        </div>
      </div>
    </div>

    <a href="{{ route('superadmin.registeruser.step', 2) }}" class="btn btn-primary">Back</a>
    
    <button type="submit" class="btn btn-primary">Next</button>
 
</form>


</div>
 

</div></div></div></div><!-----end of row----->
    



  </div>
</div>

  

  

@include('layouts.footer_content')



@endsection
