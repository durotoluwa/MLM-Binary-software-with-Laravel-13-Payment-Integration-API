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
          <a href="">Pending User Registration Payment</a>
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
  
   <h2 class="mb-4">Complete Payment for {{ $user->username }}</h2>
 
     <table class="table table-bordered">
    <tbody>
        <tr>
            <th>Full Name</th>
            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <th>Phone</th>
            <td>{{ $user->phone }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ ucfirst($user->status) }}</td>
        </tr>
        <tr>
            <th>Payment Status</th>
            <td>{{ ucfirst($user->payment_status) }}</td>
        </tr>
        <tr>
            <th>Amount to Pay</th>
            <td>₦{{ number_format(setting('registration_fee', 5000)) }}</td>
        </tr>
    </tbody>
</table>

<div class="row">
    <div class="col-md-6">
        <!-- Wallet Payment Form -->
        <form action="{{ route('user.pay.wallet', $user->id) }}" method="POST" class="mb-3">
            @csrf
            <div class="form-group">
                <label for="transaction_pin">Transaction PIN</label>
                <input type="password" name="transaction_pin" id="transaction_pin" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Pay with Wallet</button>
        </form>
    </div>
    <div class="col-md-6">

        <!-- Online Payment (Paystack Popup) -->
     <!-- Online Payment (Paystack Popup) -->
 

  <button class="btn btn-primary" id="paystackButton" style="border:none; background:none; padding:0;">
      <img src="https://www.naijatechguide.com/wp-content/uploads/2018/05/paystack-ii.png" 
         alt="Pay with Paystack" 
         style="height:80px; cursor:pointer;">
    </button>

    </div>
</div>

    <div class="mt-3">
        <a href="{{ route('user.userreg_paymentpage') }}" class="btn btn-secondary">Back to Payment Page</a>
    </div>

</div><!---end of row--->
</div>





<!-- Paystack Script -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('paystackButton').addEventListener('click', function() {
    let handler = PaystackPop.setup({
        key: '{{ env('PAYSTACK_PUBLIC_KEY') }}', // your Paystack public key
        email: '{{ $user->email }}',
        amount: {{ setting('registration_fee', 5000) * 100 }}, // raw integer in kobo
        currency: 'NGN',
        ref: '{{ \Illuminate\Support\Str::uuid() }}', // unique reference
        callback: function(response) {
            // Redirect to verify route after successful payment
            window.location.href = "{{ route('user.verifyUserPaystack', ['id' => $user->id]) }}?reference=" + response.reference;
        },
        onClose: function() {
            alert('Payment window closed.');
        }
    });
    handler.openIframe();
});
</script>




 
  </div>
</div> </div> </div>

        <!-- Container Ends-->

@include('layouts.footer_content')

 

@endsection
