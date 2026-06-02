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
          <a href="">Create Member</a>
        </li>
      </ol>
    </div>
 

 

@if (auth()->user()->isImpersonated())
    <a href="{{ route('impersonate.leave') }}" class="btn btn-danger">
        Back to Admin
    </a>
@endif



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

 
 
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                

                    <div class="card-body">

                        <!-- Progress Bar -->
                        <div class="progress mb-4">
                            <div id="registrationProgress" class="progress-bar" role="progressbar" 
                                 style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                Step 1 of 5
                            </div>
                        </div>

                        <!-- Single unified form -->
                        <form method="POST" action="{{ route('register.post') }}">
                            @csrf

                            <div class="accordion" id="registrationAccordion">

                                <!-- Step 1: Profile -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                            Step 1: Profile
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#registrationAccordion">
                                        <div class="accordion-body">
                                            <h5 class="mb-3">Step 1 of 5</h5>
                                            <div class="row">
                                                <div class="col-lg-6 mb-2">
                                                    <label>First Name</label>
                                                    <input type="text" name="first_name" class="form-control" required>
                                                </div>
                                                <div class="col-lg-6 mb-2">
                                                    <label>Last Name</label>
                                                    <input type="text" name="last_name" class="form-control" required>
                                                </div>
                                                <div class="col-lg-6 mb-2">
                                                    <label>Email</label>
                                                    <input type="email" name="email" class="form-control" required>
                                                </div>
                                                <div class="col-lg-6 mb-2">
                                                    <label>Phone</label>
                                                    <input type="number" name="phone" class="form-control" required>
                                                </div>
                                                <div class="col-lg-6 mb-2">
                                                    <label>State</label>
                                                    <input type="text" name="state" class="form-control" required>
                                                </div>
                                                <div class="col-lg-6 mb-2">
                                                    <label>City</label>
                                                    <input type="text" name="city" class="form-control" required>
                                                </div>
                                                <div class="col-lg-12 mb-2">
                                                    <label>Country</label>
                                                    <input type="text" name="country" class="form-control" required>
                                                </div>
                                                <div class="col-lg-12 mb-2">
                                                    <label>Address</label>
                                                    <input type="text" name="address" class="form-control" required>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-primary next-btn" data-next="#collapseTwo" data-step="2">Next</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Membership -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                            Step 2: Membership
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#registrationAccordion">
                                        <div class="accordion-body">
                                            <h5 class="mb-3">Step 2 of 5</h5>
                                            <label>Upline Username</label>
                                            <input type="text" name="upline_username" class="form-control" required>
                                            <label>Sponsor Username</label>
                                            <input type="text" name="sponsor_username" class="form-control">
                                            <button type="button" class="btn btn-secondary back-btn" data-back="#collapseOne" data-step="1">Back</button>
                                            <button type="button" class="btn btn-primary next-btn" data-next="#collapseThree" data-step="3">Next</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Next of Kin -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                            Step 3: Next of Kin
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#registrationAccordion">
                                        <div class="accordion-body">
                                            <h5 class="mb-3">Step 3 of 5</h5>
                                            <label>Full Name</label>
                                            <input type="text" name="kin_name" class="form-control">
                                            <label>Phone</label>
                                            <input type="text" name="kin_phone" class="form-control">
                                            <label>Email</label>
                                            <input type="email" name="kin_email" class="form-control">
                                            <label>Address</label>
                                            <input type="text" name="kin_address" class="form-control">
                                            <button type="button" class="btn btn-secondary back-btn" data-back="#collapseTwo" data-step="2">Back</button>
                                            <button type="button" class="btn btn-primary next-btn" data-next="#collapseFour" data-step="4">Next</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 4: Bank Details -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFour">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                            Step 4: Bank Details
                                        </button>
                                    </h2>
                                    <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#registrationAccordion">
                                        <div class="accordion-body">
                                            <h5 class="mb-3">Step 4 of 5</h5>
                                            <label>Bank Name</label>
                                            <input type="text" name="bank_name" class="form-control" required>
                                            <label>Account Number</label>
                                            <input type="text" name="account_no" class="form-control" required>
                                            <label>Account Name</label>
                                            <input type="text" name="account_name" class="form-control" required>
                                            <button type="button" class="btn btn-secondary back-btn" data-back="#collapseThree" data-step="3">Back</button>
                                            <button type="button" class="btn btn-primary next-btn" data-next="#collapseFive" data-step="5">Next</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 5: Security -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingFive">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                                            Step 5: Security
                                        </button>
                                    </h2>
                                    <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#registrationAccordion">
                                        <div class="accordion-body">
                                            <h5 class="mb-3">Step 5 of 5</h5>
                                            <label>Username</label>
                                            <input type="text" name="username" class="form-control" required>
                                            <label>Password</label>
                                            <input type="password" name="password" class="form-control" required>
                                            <label>Confirm Password</label>
                                            <input type="password" name="password_confirmation" class="form-control" required>
                                            <button type="button" class="btn btn-secondary back-btn" data-back="#collapseFour" data-step="4">Back</button>
                                            <button type="submit" class="btn btn-success">Register</button>
                                        </div>
                                    </div>
                                </div>

                            </div> <!-- end accordion -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
 

<script>
// Function to update progress bar
function updateProgress(step) {
    const progressBar = document.getElementById('registrationProgress');
    const percentage = (step / 5) * 100;
    progressBar.style.width = percentage + '%';
    progressBar.setAttribute('aria-valuenow', percentage);
    progressBar.textContent = `Step ${step} of 5`;
}

// Handle Next button clicks
document.querySelectorAll('.next-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const target = document.querySelector(this.dataset.next);
        const collapse = new bootstrap.Collapse(target, { toggle: true });
        collapse.show();
        updateProgress(this.dataset.step);
    });
});

// Handle Back button clicks
document.querySelectorAll('.back-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const target = document.querySelector(this.dataset.back);
        const collapse = new bootstrap.Collapse(target, { toggle: true });
        collapse.show();
        updateProgress(this.dataset.step);
    });
});
</script>


 
  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
