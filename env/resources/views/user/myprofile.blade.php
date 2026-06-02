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
          <a href="">Profile</a>
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


 <!--=====================  end Page Title Start Here =====================-->
  <div class="row">
      <div class="col-lg-12">
        <div class="profile card card-body px-3 pt-3 pb-0">
          <div class="profile-head">
            <div class="photo-content">
              <div class="cover-photo rounded"></div>
            </div>
            <div class="profile-info">
              <div class="profile-photo">
          <img src=" "   class="img-fluid rounded-circle" alt="">
            
              </div>
              <div class="profile-details">
                <div class="profile-name px-3 pt-2">
                  <h4 class="text-primary mb-0">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h4>
                  <p>Name</p>
                </div>
                <div class="profile-email px-2 pt-2">
                  <h4 class="text-muted mb-0">{{ Auth::user()->email }}</h4>
                  <p>Email</p>
                </div>
               
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
 <div class="row">
          <div class="col-xl-12">
            <div class="card">
              <div class="card-body">
                <div class="profile-tab">
                  <div class="custom-tab-1">
                    <ul class="nav nav-tabs">
                      <li class="nav-item">
                        <a href="#my-posts" data-bs-toggle="tab" class="nav-link active show">Update Profile</a>
                      </li>

                      <li class="nav-item">
                        <a href="#my-bank" data-bs-toggle="tab" class="nav-link ">Bank Details</a>
                      </li>  
                      <li class="nav-item">
                        <a href="#about-me" data-bs-toggle="tab" class="nav-link">Change Password</a>
                      </li>
                    
                    </ul>
                    <div class="tab-content">
                      <div id="my-posts" class="tab-pane fade active show">
                        <div class="my-post-content pt-3">
 <h4 class="card-title">Update Profile</h4>
<form action="{{ route('updateProfile',Auth::user()->id)}}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')
<div class="row">
<div class="mb-3 col-md-6">
<label class="form-label">First Name</label>
<input value="{{ Auth::user()->first_name }}" type="text"  class="form-control" name="first_name">
</div>

<div class="mb-3 col-md-6">
<label class="form-label">Last Name</label>
<input value="{{ Auth::user()->last_name }}" type="text"   class="form-control" name="last_name">
</div>
       
                              
                               
<div class="mb-3 col-md-6">
<label class="form-label">Phone</label>
<input value="{{ Auth::user()->phone }}" type="text"  class="form-control" name="phone">
</div>

                               
<div class="mb-3 col-md-6">
<label class="form-label">Email</label>
<input value="{{ Auth::user()->email }}" type="email" placeholder="Email" class="form-control" name="email">
</div>

<div class="mb-3">
    <label class="form-label">Profile Picture</label>
    <div class="image-frame2" id="imagePreview" style="margin-bottom: 30px;">
        @auth
            <img src="" id="previewImage4" alt="Profile Picture">
        @else
            <span id="placeholderText">No image selected</span>
        @endauth
    </div>

   


    <input class="form-control" name="profile_photo_path" type="file" id="imageInput" accept="image/*">
</div>

                    @if ($errors->has('profile_photo_path'))
                    <div class="text-danger">
                        {{ $errors->first('profile_photo_path') }}
                    </div>
                    @endif
                              
                             <div class="mb-3 col-md-6">
                              <button class="btn btn-primary" type="submit">Update Profile</button>
                             </div>
                            </form>


                          </div>
                        </div>
                      </div>


                      <div id="my-bank" class="tab-pane fade">
                        <div class="pt-3">
                          <div class="profile-settings">
                            <h4 class="card-title">Bank Details</h4>
 

    <form action="{{ route('updateBankDetails',Auth::user()->id)}}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')
    <div class="row">
<div class="col-12 mb-2">
    <div class="form-group mb-3">
        <label class="form-label required">Bank Name</label>
        <select name="bank_code" class="form-control" required>
            <option value="">-- Select Bank --</option>
            @foreach($banks as $bank)
                <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}"
                    {{ $user->bank_code == $bank['code'] ? 'selected' : '' }}>
                    {{ $bank['name'] }}
                </option>
            @endforeach
        </select>
        <input type="hidden" name="bank_name" id="bank_name" value="{{ $user->bank_name }}">
    </div>
</div>



        <div class="col-12 mb-2">
            <div class="form-group mb-3">
                <label class="form-label required">Account Number</label>
                <input type="text" name="account_no" class="form-control" value="{{ old('account_no', $user->account_no ?? '') }}" required>
            </div>
        </div>

      <div class="col-12 mb-2">
    <div class="form-group mb-3">
        <label class="form-label required">Account Name</label>
        <input type="text" name="account_name"   class="form-control" 
               value="{{ old('account_no', $user->account_name ?? '') }}" required>
    </div>
</div>

    </div>
    <button class="btn btn-primary" type="submit">Update Bank Details</button>
</form>



                          </div>
                        </div>  
                      </div>












                      <div id="about-me" class="tab-pane fade">
                           <div class="pt-3">
                          <div class="profile-settings">
                            <h4 class="card-title">Change Password</h4>
                           <form method="POST" action="{{ route('user.updatePassword') }}">
    @csrf

    <div class="mb-3 col-md-12">
        <label class="form-label">New Password</label>
        <input type="password" name="password" placeholder="New Password" class="form-control" required>
    </div>

    <div class="mb-3 col-md-12">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" class="form-control" required>
    </div>

    <button class="btn btn-primary" type="submit">Change Password</button>
</form>

                          </div>
                        </div>
                      </div>





                   
                    </div>
                  </div>


</div><!--==== Container-fluid Ends ======-->
</div> <!--========= Container Ends ========-->

 
  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')

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


 <script>
document.querySelector('select[name="bank_code"]').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('bank_name').value = selectedOption.getAttribute('data-name');
});
</script>




@endsection
