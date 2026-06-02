
<!DOCTYPE html>
<html lang="en">
<head>
 @include('admin.headerlink')

</head>
<body>
    <div class="animated-bg">
    <div class="fix-wrapper">
        <div class="container">
            
<!-- STEP 1: Profile Information -->
<form method="POST" action="{{ route('register.step.post', 1) }}">
  @csrf
  <div class="tab-pane" id="step1">
    <h5 class="mb-3">Profile</h5>
    <div class="row">
      <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">First Name</label>
          <input type="text" name="first_name" class="form-control" required>
        </div>
      </div>
      <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Last Name</label>
          <input type="text" name="last_name" class="form-control" required>
        </div>
      </div>
      <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Email Address</label>
          <input type="email" name="email" class="form-control" required>
        </div>
      </div>
      <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Phone Number</label>
          <input type="number" name="phone" class="form-control" required>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Next</button>
  </div>
</form>

<!-- STEP 2: Membership -->
<form method="POST" action="{{ route('register.step.post', 2) }}">
  @csrf
  <div class="tab-pane" id="step2">
    <h5 class="mb-3">Membership</h5>
    <div class="row">
      <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Upline Username</label>
          <input type="text" name="upline_username" class="form-control" required>
        </div>
      </div>
      <div class="col-lg-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Sponsor Username</label>
          <input type="text" name="sponsor_username" class="form-control" required>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Next</button>
  </div>
</form>

<!-- STEP 3: Next of Kin -->
<form method="POST" action="{{ route('register.step.post', 3) }}">
  @csrf
  <div class="tab-pane" id="step3">
    <h5 class="mb-3">Next of Kin</h5>
    <div class="row">
      <div class="col-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Full Name</label>
          <input type="text" name="kin_name" class="form-control" required>
        </div>
      </div>
      <div class="col-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Phone Number</label>
          <input type="text" name="kin_phone" class="form-control" required>
        </div>
      </div>
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Email</label>
          <input type="email" name="kin_email" class="form-control" required>
        </div>
      </div>
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Address</label>
          <input type="text" name="kin_address" class="form-control" required>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Next</button>
  </div>
</form>

<!-- STEP 4: Bank Details -->
<form method="POST" action="{{ route('register.step.post', 4) }}">
  @csrf
  <div class="tab-pane" id="step4">
    <h5 class="mb-3">Bank Details</h5>
    <div class="row">
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Bank Name</label>
          <input type="text" name="bank_name" class="form-control" required>
        </div>
      </div>
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Account Number</label>
          <input type="text" name="account_no" class="form-control" required>
        </div>
      </div>
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Account Name</label>
          <input type="text" name="account_name" class="form-control" required>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Next</button>
  </div>
</form>

<!-- STEP 5: Account Security -->
<form method="POST" action="{{ route('register.step.post', 5) }}">
  @csrf
  <div class="tab-pane" id="step5">
    <h5 class="mb-3">Account Security</h5>
    <div class="row">
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
      </div>
      <div class="col-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
      </div>
      <div class="col-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Confirm Password</label>
          <input type="password" name="password_confirmation" class="form-control" required>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-success">Register</button>
  </div>
</form>



      </div>
    </div>
  </div>
</div>
</div>





        </div>
    </div></div>
 

    @include('admin.footer')
    <script>
		$(document).ready(function(){
			// SmartWizard initialize
			$('#smartwizard').smartWizard(); 
		});
	</script>

</body>

</html>