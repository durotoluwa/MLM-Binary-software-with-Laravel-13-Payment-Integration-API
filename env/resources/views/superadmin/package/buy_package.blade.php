@extends('layouts.app')
@section('content')


@include('layouts.navbar')
 
    <style>
.select2-container .select2-selection--single {
    height: 38px !important;
    padding: 6px 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
    right: 10px;
}
</style>

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
            <a href="">Buy Package</a>
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


 <form action="{{ route('superadmin.package.buy_package.store') }}" method="POST">
                @csrf
<div class="form-group mb-3">
    <label for="user_id">Select User</label>
    <select name="user_id" id="user_id" class="form-control" required>
        <option value="">-- Choose User --</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->first_name }} {{ $user->last_name }})</option>
        @endforeach
    </select>
</div>

<div id="last-package-info" class="alert d-none"></div>

<div class="form-group mb-3">
    <label for="package_id">Select Package</label>
    <select name="package_id" id="package_id" class="form-control" required>
        <option value="">-- Select Package --</option>
    </select>
</div>

  <button type="submit" class="btn btn-success">Confirm Purchase</button>
            </form>


  </div>
</div>

        <!-- Container Ends-->
<script>
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                imagePreview.innerHTML = `<img src="${e.target.result}" alt="Uploaded Image">`;
            };

            reader.readAsDataURL(file);
        } else {
            imagePreview.innerHTML = '<span>No image selected</span>';
        }
    });
</script>

@include('layouts.footer_content')


 
  
  <script>
    ClassicEditor
      .create(document.querySelector('#editor'))
      .catch(error => {
        console.error(error);
      });
  </script>

@endsection
