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
            <a href="">Edit Package</a>
          </li>
        </ol>
      </div>
<a  href="{{ route('superadmin.package.package_list') }}" class="btn btn-primary" style="margin-bottom: 20px;"><i class="fa-solid fa-list"></i> Package List</a>
 
 

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
<form action="{{ route('updatePackage',$package->id)}}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
 
            <div class="col-xl-6">
            <div class="card h-auto">
            <div class="card-body">
                                <div class="mb-3">
                        <label class="form-label">Feature Image</label>
                        <div class="image-frame2" id="imagePreview" style="margin-bottom: 30px;">
                            @if ($package->package_image)
                            <img src="{{ asset($package->package_image) }}" alt="" id="previewImage4">
                        @else
                            <span id="placeholderText">No image selected</span>
                        @endif
                        </div>
                        <input class="form-control" name="package_image" type="file" id="imageInput" accept="image/*">
                    </div>
                    @if ($errors->has('package_image'))
                    <div class="text-danger">
                        {{ $errors->first('package_image') }}
                    </div>
                    @endif
            </div><!--=== Card Body Ends ====-->
            </div><!--==== Card Ends =======-->
            </div><!--===== Col Ends =====-->

<div class="row">
<div class="col-xl-6">
<div class="mb-3">
<label class="form-label">Package Name</label>
<input type="text" value="{{ $package->packageName }}" class="form-control" name="packageName"  placeholder="Enter Package Name" required>
</div></div>



<div class="col-xl-6">
<div class="mb-3">
<label class="form-label">Package Price</label>
<input type="text" class="form-control" name="price" value="{{ $package->price }}"  placeholder="Enter Package Price" required>
</div></div>


<div class="col-xl-4">
<div class="mb-3">
<label class="form-label">Package APC</label>
<input type="text" class="form-control" name="apc" value="{{ $package->apc }}"  placeholder="Enter Package Price" required>
</div></div>
<div class="col-xl-4">
<div class="mb-3">
<label class="form-label">Package Bottle</label>
<input type="text" class="form-control" name="bottle" value="{{ $package->bottle }}"  placeholder="Enter Package bottle" required>
</div></div>

<div class="col-xl-4">
<div class="mb-3">
<label class="form-label">Package cpts</label>
<input type="text" class="form-control" name="cpts" value="{{ $package->cpts }}"  placeholder="Enter Package cpts" required>
</div></div>


<div class="col-xl-12">
<div class="mb-3">
<label class="form-label">Package Description </label>
<textarea rows="4" name="description" class="form-control" name="description">  {{ $package->description }}</textarea>
</div></div>


  <div class="mb-3">
                <label class="form-label"> Status</label>
                <select name="status" class="form-control wide">
       

                     <option value="active" @if($package->status == 'active') selected @endif>Active</option>
                    <option value="hidden" @if($package->status == 'inactive') selected @endif>Inactive</option>
                  
                    
                  </select>
              </div>

</div>

<button type="submit" class="btn btn-sm btn-primary mb-4 open">Update Package</button>

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
