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
                        <h4 class="card-title mt-3">Step 5: Security</h4>
                    </div>
<div class="container py-5">

       
 <form method="POST" action="{{ route('user.downline.post', 5) }}">
    @csrf

    <div class="row">
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Username</label>
          <input type="text" name="username" class="form-control" value="{{ old('username', $data['username'] ?? '') }}"  required>
          
        </div>
      </div>
      <div class="col-6 mb-2" hidden>
        <div class="form-group mb-3">
          <label class="form-label required">Password</label>
          <input type="password" name="password" class="form-control" value="secret">
        </div>
      </div>
      <div class="col-6 mb-2" hidden>
        <div class="form-group mb-3">
          <label class="form-label required">Confirm Password</label>
          <input type="password" name="password_confirmation" class="form-control" value="secret">
        </div>
      </div>
    </div>

    <a href="{{ route('user.downline.step', 4) }}" class="btn btn-primary">Back</a>
     <button type="submit" class="btn btn-primary">Next</button>
     
</form>

</div>
 

</div></div></div></div><!-----end of row----->
    



  </div>
</div>

  

  

@include('layouts.footer_content')



@endsection
