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


<div class="container py-5">
 

    <form id="multiStepForm" method="POST" action=" ">
        @csrf

        <!-- Step 1 -->
        <div class="form-step active">
            <h4 class="mb-3">Step 1: Personal Information</h4>

            <div class="row">
                
             <div class="col-lg-6 mb-2">
        <div class="form-group    mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div></div>
        <div class="col-lg-6 mb-2">
        <div class="form-group    mb-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
            </div></div>


             <div class="col-lg-6 mb-2">
        <div class="form-group    mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div></div>
             <div class="col-lg-6 mb-2">
        <div class="form-group    mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" required>
            </div></div>

            <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">State</label>
          <input type="text" name="state" class="form-control" value="{{ old('state', $data['state'] ?? '') }}" required>
                  </div>
      </div>
      <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">City</label>
          <input type="text" name="city" class="form-control" value="{{ old('city', $data['city'] ?? '') }}" required>  
                  </div>
      </div>

 <div class="col-lg-12 mb-2">
    <div class="form-group mb-3">
        <label class="form-label required">Country</label>
        <select name="country" class="form-control" required>
            <option value="">-- Select Country --</option>
            @php
                $countries = [
                    "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", 
                    "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", 
                    "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", 
                    "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia", "Cameroon", "Canada", 
                    "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo", 
                    "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", 
                    "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", 
                    "Eswatini", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", 
                    "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", 
                    "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", 
                    "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", 
                    "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", 
                    "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", 
                    "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", 
                    "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", 
                    "North Korea", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", 
                    "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", 
                    "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", 
                    "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", 
                    "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Korea", "South Sudan", 
                    "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", 
                    "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", 
                    "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", 
                    "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"
                ];
            @endphp

            @foreach($countries as $country)
                <option value="{{ $country }}" {{ old('country', $data['country'] ?? '') == $country ? 'selected' : '' }}>
                    {{ $country }}
                </option>
            @endforeach
        </select>
    </div>
</div>


      <div class="col-lg-12 mb-2">
        <div class="form-group    mb-3">
          <label class="form-label required">Address</label>
          <input type="text" name="address" class="form-control" value="{{ old('address', $data['address'] ?? '') }}" required>
        </div>
      </div>

            </div>

        </div>

        <!-- Step 2 -->
        <div class="form-step">
            <h4 class="mb-3">Step 2: Upline & Sponsor</h4>
            <div class="mb-3">
                <label>Upline Username</label>
                <input type="text" name="upline_username" class="form-control">
            </div>
            <div class="mb-3">
                <label>Sponsor Username</label>
                <input type="text" name="sponsor_username" class="form-control">
            </div>
        </div>

        <!-- Step 3 -->
        <div class="form-step">
            <h4 class="mb-3">Step 3: Next of Kin</h4>
            <div class="mb-3">
                <label>Kin Full Name</label>
                <input type="text" name="kin_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Kin Phone</label>
                <input type="text" name="kin_phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Kin Email</label>
                <input type="email" name="kin_email" class="form-control">
            </div>
            <div class="mb-3">
                <label>Kin Address</label>
                <input type="text" name="kin_address" class="form-control">
            </div>
        </div>

        <!-- Step 4 -->
        <div class="form-step">
            <h4 class="mb-3">Step 4: Bank Details</h4>
            <div class="mb-3">
                <label>Bank Name</label>
                <input type="text" name="bank_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Account Number</label>
                <input type="text" name="account_no" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Account Name</label>
                <input type="text" name="account_name" class="form-control" required>
            </div>
        </div>

        <!-- Step 5 -->
        <div class="form-step">
            <h4 class="mb-3">Step 5: Security</h4>
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
        </div>

        <!-- Navigation buttons -->
        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-secondary" id="prevBtn">Back</button>
            <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
            <button type="submit" class="btn btn-success d-none" id="submitBtn">Create User</button>
        </div>
    </form>
</div>

<!-- Simple styling -->
<style>
    .form-step { display: none; }
    .form-step.active { display: block; }
</style>

<!-- Step navigation script -->
<script>
    const steps = document.querySelectorAll(".form-step");
    const nextBtn = document.getElementById("nextBtn");
    const prevBtn = document.getElementById("prevBtn");
    const submitBtn = document.getElementById("submitBtn");
    let currentStep = 0;

    function showStep(step) {
        steps.forEach((s, i) => s.classList.toggle("active", i === step));
        prevBtn.style.display = step === 0 ? "none" : "inline-block";
        nextBtn.style.display = step === steps.length - 1 ? "none" : "inline-block";
        submitBtn.classList.toggle("d-none", step !== steps.length - 1);
    }

    nextBtn.addEventListener("click", () => {
        if (currentStep < steps.length - 1) currentStep++;
        showStep(currentStep);
    });

    prevBtn.addEventListener("click", () => {
        if (currentStep > 0) currentStep--;
        showStep(currentStep);
    });

    showStep(currentStep);
</script>
    

</div></div></div></div><!-----end of row----->
    



  </div>
</div>

  

  

@include('layouts.footer_content')



@endsection
