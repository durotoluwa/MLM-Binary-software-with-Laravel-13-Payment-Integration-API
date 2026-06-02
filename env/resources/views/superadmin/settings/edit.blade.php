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
            <a href="">Admin settings</a>
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
<div class="card h-auto">
<div class="card-body">

 <form action="{{ route('superadmin.settings.update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="registration_fee" class="form-label">Registration Fee</label>
            <input type="number" name="registration_fee" id="registration_fee" class="form-control" value="{{ old('registration_fee', $settings['registration_fee']) }}" required>
        </div>

              <div class="mb-3">
            <label for="usd_conversion_rate" class="form-label">Conversion Rate (NGN - USD)</label>
            <input type="number" name="usd_conversion_rate" id="usd_conversion_rate" class="form-control" value="{{ old('usd_conversion_rate', $settings['usd_conversion_rate']) }}" required>
        </div>
 

        <button type="submit" class="btn btn-primary">Update Settings</button>
    </form>
          
</div>
</div></div></div></div><!-----end of row----->
    



  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
