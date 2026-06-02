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
                        <h4 class="card-title mt-3">Step 1: Profile</h4>
                    </div>
<div class="container py-5">

       
 
       <form method="POST" action="{{ route('superadmin.registeruser.post', 1) }}">
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
      <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">First Name</label>
          <input type="text"name="first_name" value="{{ old('first_name', $data['first_name'] ?? '') }}"class="form-control" required>
        </div>
      </div>
      <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Last Name</label>
          <input type="text" name="last_name" value="{{ old('last_name', $data['last_name'] ?? '') }}" class="form-control" required>
        </div>
      </div>
      <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Email Address</label>
          <input type="email" name="email" value="{{ old('email', $data['email'] ?? '') }}" class="form-control" required>
        </div>
      </div>
      <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Phone Number</label>
          <input type="number" name="phone" class="form-control" value="{{ old('phone', $data['phone'] ?? '') }}" required>
        </div>
      </div>

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
    <button type="submit" class="btn btn-primary">Next</button>
      
</form>
</div>
 

</div></div></div></div><!-----end of row----->
    



  </div>
</div>

  

  

@include('layouts.footer_content')



@endsection
