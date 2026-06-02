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
            <a href="">Edit {{ $user->first_name }} {{ $user->last_name }} Profile</a>
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
<div class="container py-5">
 

    <form id="editProfileForm" action="{{ route('superadmin.member.updateuserprofile', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Step 1: Personal Information -->
        <div class="form-step active">
            <h4 style="margin-bottom: 40px">Personal Information</h4>

             <div class="mb-3">
                <label for="name" class="form-label">Username</label>
                <input type="text" class="form-control" id="name" name="username" value="{{ $user->username }}"  >
            </div>


            <div class="mb-3">
                <label for="name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="name" name="first_name" value="{{ $user->first_name }}"  >
            </div>
             <div class="mb-3">
                <label for="name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="name" name="last_name" value="{{ $user->last_name }}"  >
            </div>
             <div class="mb-3">
                <label for="name" class="form-label">Phone No.</label>
                <input type="text" class="form-control" id="name" name="phone" value="{{ $user->phone }}"  >
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}"  >
            </div>
       
             <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="{{ $user->address }}"  > 
            </div> 
            
             <div class="mb-3">
                <label for="state" class="form-label">State</label>
                <input type="text" class="form-control" id="state" name="state" value="{{ $user->state }}"  >   
            </div>
                 <div class="mb-3">
                <label for="City" class="form-label">City</label>
                <input type="text" class="form-control" id="city" name="city" value="{{ $user->city }}"  >   
            </div>
            
             <div class="mb-3">
                <label for="country" class="form-label">Country</label>
                <input type="text" class="form-control" id="country" name="country" value="{{ $user->country }}"  >
            </div>

            <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
        </div>

         <!-- Step 2: Bank Details -->
        <div class="form-step">
            <h4 style="margin-bottom: 40px">Bank Details</h4>
            <div class="mb-3">
                <label for="bank_name" class="form-label">Bank Name</label>
                <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ $user->bank_name }}"  >
            </div>
             <div class="mb-3">
                <label for="account_no" class="form-label">Account Number</label>
                <input type="text" class="form-control" id="account_no" name="account_no" value="{{ $user->account_no }}"  >
            </div>
             <div class="mb-3">
                <label for="account_name" class="form-label">Account Name</label>
                <input type="text" class="form-control" id="account_name" name="account_name" value="{{ $user->account_name }}"  >
            </div>
            <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
            <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>

        </div>
            <!-- Step 3: Change Password -->
        <div class="form-step">
            <h4 style="margin-bottom: 40px">Change Password</h4>
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" >
            </div>
             <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" >   
            </div>
            <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
            <button type="submit" class="btn btn-success">Update Profile</button>

    
    </form>

    <script>
        let currentStep = 0;
        const formSteps = document.querySelectorAll('.form-step');

        function showStep(step) {
            formSteps.forEach((formStep, index) => {
                formStep.classList.toggle('active', index === step);
            });
        }

        function nextStep() {
            if (currentStep < formSteps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        }

        function prevStep() {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        }
    </script>
 
</div>

<!-- Simple styling -->
<style>
    .form-step { display: none; }
    .form-step.active { display: block; }
</style>

 

</div></div></div></div><!-----end of row----->
    



  </div>
</div>

  

  

@include('layouts.footer_content')



@endsection
