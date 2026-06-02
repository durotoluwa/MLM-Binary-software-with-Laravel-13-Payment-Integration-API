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
                        <h4 class="card-title mt-3">Step 6: Payment</h4>
                    </div>
<div class="container py-5">

       
-<form method="POST" action="{{ route('register.post', 6) }}">
    @csrf

    <div class="row">
        <div class="col-12 mb-3">
            <label class="form-label required">Select Payment Method</label>
            <select name="payment_method" id="payment_method" class="form-control" required>
                <option value="">-- Select Payment Method --</option>
                <option value="wallet">Deposit Wallet</option>
                <option value="paystack">Paystack Online Payment</option>
            </select>
        </div>
    </div>

    <!-- Wallet Payment Section -->
    <div id="wallet_section" style="display:none;">
        <div class="form-group mb-3">
            <label class="form-label required">Transaction PIN</label>
            <input type="password" name="transaction_pin" class="form-control" placeholder="Enter your transaction PIN">
        </div>
        <p class="text-muted">
            Your deposit wallet balance will be checked. Ensure you have enough funds.
        </p>
    </div>

    <!-- Paystack Payment Section -->
    <div id="paystack_section" style="display:none;">
        <p class="text-muted">
            You will be redirected to Paystack to complete your payment securely.
        </p>
    </div>

    <a href="{{ route('user.downline.step', 5) }}" class="btn btn-primary">Back</a>
    
    <button type="submit" class="btn btn-primary">Proceed</button>
</form>

</div>
 

</div></div></div></div><!-----end of row----->
    



  </div>
</div>

  

  

@include('layouts.footer_content')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const paymentSelect = document.getElementById('payment_method');
    const walletSection = document.getElementById('wallet_section');
    const paystackSection = document.getElementById('paystack_section');

    paymentSelect.addEventListener('change', function () {
        if (this.value === 'wallet') {
            walletSection.style.display = 'block';
            paystackSection.style.display = 'none';
        } else if (this.value === 'paystack') {
            walletSection.style.display = 'none';
            paystackSection.style.display = 'block';
        } else {
            walletSection.style.display = 'none';
            paystackSection.style.display = 'none';
        }
    });
});
</script>

@endsection
