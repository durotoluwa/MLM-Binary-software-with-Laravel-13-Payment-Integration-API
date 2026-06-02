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
          <a href="">Topup Wallet</a>
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
<div class="card">
<div class="card-body">
  
 <form id="walletTopupForm" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label>Enter Amount:</label>
        <input type="number" name="amount" id="topup-amount" required class="form-control">
    </div>

    <div class="form-group">
        <label>Select Payment Method:</label>
        <select name="payment_method" id="topup-method" class="form-control" required>
            <option value="">-- Select --</option>
            <option value="bank">Bank Transfer</option>
            <option value="online">Online Payment</option>
        </select>
    </div>

    <div id="bank-fields" style="display: none; margin-top: 20px;">
        <div class="form-group mb-3">
            <label>Bank Name</label>
            <input type="text" name="bank_name" class="form-control">
        </div>
        <div class="form-group mb-3">
            <label>Account Name</label>
            <input type="text" name="account_name" class="form-control">
        </div>
        <div class="form-group mb-3">
            <label>Payment Proof (Image)</label>
            <input type="file" name="payment_proof" class="form-control">
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-3" id="submitBtn">Submit</button>
</form>



 
  </div>
</div> </div> </div>

        <!-- Container Ends-->

@include('layouts.footer_content')

<script>
document.getElementById('topup-method').addEventListener('change', function () {
    const bankFields = document.getElementById('bank-fields');
    bankFields.style.display = this.value === 'bank' ? 'block' : 'none';
});
</script>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('topup-method').addEventListener('change', function () {
    const bankFields = document.getElementById('bank-fields');
    const submitBtn = document.getElementById('submitBtn');

    if (this.value === 'bank') {
        bankFields.style.display = 'block';
        submitBtn.type = 'submit';
    } else {
        bankFields.style.display = 'none';
        submitBtn.type = 'button';
    }
});

document.getElementById('submitBtn').addEventListener('click', function () {
    const method = document.getElementById('topup-method').value;
    const amount = document.getElementById('topup-amount').value;

    if (method === 'online') {
        let handler = PaystackPop.setup({
            key: '{{ env("PAYSTACK_PUBLIC_KEY") }}',
            email: '{{ Auth::user()->email }}',
            amount: amount * 100,
            currency: 'NGN',
            ref: '{{ \Illuminate\Support\Str::uuid() }}',
            callback: function (response) {
                window.location.href = "{{ route('user.wallet.topup.verify') }}?reference=" + response.reference + "&amount=" + amount;
            },
            onClose: function () {
                alert('Payment window closed.');
            }
        });
        handler.openIframe();
    }
});
</script>


@endsection
