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
                        <h4 class="card-title mt-3">Step 2: Membership</h4>
                    </div>
<div class="container py-5">

  <form method="POST" action="{{ route('superadmin.registermember.post', 2) }}">
    @csrf
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
<div id="upline_group"> 
  
    <label for="upline_username">Upline Username</label>
    <input type="text" name="upline_username"
        id="upline_username"
        value="{{ old('upline_username', $data['upline_username'] ?? '') }}"
        class="form-control" required>

    <small id="upline_info" class="text-success" style=" padding:5px; border:solid 1px #F0F0F0; line-height:60px; font-size:17px; font-weight:600;"></small>
    <span id="upline_error" class="text-danger " style=" padding:5px; border:solid 1px #F0F0F0; line-height:60px; font-size:17px; font-weight:600;"></span>
</div>


<div class="form-group mb-3" id="sponsor_group">

  <div id="sponsor_error" style="color: red;"></div>

  <label class="form-label required">Sponsor Username</label>
  <input type="text" id="sponsor_username" name="sponsor_username" class="form-control">
   
</div>

</div>


 

      <div class="col-lg-6 mb-2">
    <a href="{{ route('superadmin.registermember.step', 1) }}" class="btn btn-primary">Back</a>
    
    <button type="submit" class="btn btn-primary">Next</button>
    
      </div>
 
</form>

</div>
 

</div></div></div></div><!-----end of row----->
    



  </div>
</div>

  

  

@include('layouts.footer_content')



@endsection
