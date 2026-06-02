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
          <a href="">Checkout Page</a>
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
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
<form id="checkoutForm" action="{{ route('checkout.submit') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <h5 class="mb-3">Order Summary</h5>
 <ul class="list-group mb-3">
    @php
        $total = 0;
        $totalCtp = 0;
    @endphp

    @foreach($cart as $item)
        @php
            $itemApc = $item['apc'] ?? 0;
            $itemPrice = $item['price'] + $itemApc;
            $singleitemPrice = $item['price'];
            $itemTotal = $itemPrice * $item['quantity'];
            $itemCtp = ($item['cpts'] ?? 0) * $item['quantity'];
            $total += $itemTotal;
            $totalCtp += $itemCtp;
        @endphp

        <li class="list-group-item d-flex justify-content-between align-items-start flex-column">
            <div class="d-flex justify-content-between w-100">
                <div>
                    {{ $item['name'] }} (x{{ $item['quantity'] }})
                </div>
                <div>
                    ₦{{ number_format($itemTotal, 2) }}
                </div>
            </div>
            <small class="text-muted">CTP: {{ $item['cpts'] ?? 0 }} x {{ $item['quantity'] }} = {{ $itemCtp }}</small>
        </li>
    @endforeach

      <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">
        Total CPT
        <span>{{ $totalCtp }} CPT</span>
    </li>

    <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">
        Total
        <span>₦{{ number_format($total, 2) }}</span>
    </li>

  
</ul>
<div class="mb-3">
    <label>Order For</label>
    <select name="order_for" id="order_for" class="form-control">
        <option value="">-- Select --</option>
        <option value="self">Individual</option>
        <option value="member">Member</option>
    </select>
</div>

<div class="mb-3 d-none" id="member_username_box">
    <label>Member Username</label>
    <input type="text" name="member_username" class="form-control" placeholder="Enter Member username">
</div>

  <div id="payment_section" class="d-none">
        <label for="payment_method" class="form-label">Select Payment Method</label>
    <select name="payment_method" class="form-control" required id="payment-method-select">
        <option value="">-- Select --</option>
        <option value="wallet">Deposit Wallet</option>
        <option value="bank">Bank Transfer</option>
        <option value="online">Online Payment (Flutterwave)</option>
      </select>

    </div>




<div id="bankName" style="display:none; margin-top:40px;" class="mb-3" > <!-- Example -->
  <label>Bank Name</label>
  <input type="text" name="bank_name" class="form-control">
</div>

<div id="acctName" style="display:none;" class="mb-3"> <!-- Example -->
  <label>Account Name</label>
  <input type="text" name="account_name" class="form-control">
</div>

<div id="Amount" style="display:none;" class="mb-3"> <!-- Example -->
  <label>Amount</label>
    <input type="text" name="amount" class="form-control" value="{{ $total }}" readonly>
      <input type="hidden" name="singleprice" class="form-control" value="{{ $singleitemPrice }}">
</div>

<div id="proof-upload" style="display:none;" class="mb-3"> <!-- Example -->
  <label>Proof of Payment</label>
  <input type="file" name="proof" class="form-control">
</div>

 <input type="hidden" name="order_for" value="self">

<div id="wallet-pin-group" style="display: none; margin-top:40px; margin-bottom:40px;" class="mb-3">
  <label for="transaction_pin">Transaction PIN</label>
  <input type="text" name="transaction_pin" class="form-control" placeholder="Enter your PIN">
</div>
  <input type="hidden" name="singleprice" class="form-control" value="{{ $singleitemPrice }}">
<input type="hidden" id="totalAmount" value="{{ $total }}">
    <button type="submit" id="confirmPayBtn" class="btn btn-primary">Confirm & Pay</button>
</form>


                </div></div></div>


 
    </div>
    <!--========end row==========-->

 
  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')


<script>
    // Remove single item from cart
    document.querySelectorAll('.btn-remove-cart').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;

            fetch(`/cart/remove/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => location.reload());
        });
    });

    // Clear all items from cart
    document.querySelector('.btn-clear-cart')?.addEventListener('click', function () {
        if (confirm('Are you sure you want to clear the entire cart?')) {
            fetch(`/cart/clear`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => location.reload());
        }
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const select = document.getElementById('payment-method-select');
  const walletPin = document.getElementById('wallet-pin-group');

  const bankFields = ['bankName', 'acctName', 'Amount', 'proof-upload'];

  function handlePaymentChange() {
    const selected = select.value;

    // Show/hide wallet pin input
    walletPin.style.display = (selected === 'wallet') ? 'block' : 'none';

    // Show/hide bank transfer fields
    bankFields.forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.style.display = (selected === 'bank') ? 'block' : 'none';
      }
    });
  }

  select.addEventListener('change', handlePaymentChange);
  handlePaymentChange(); // Initial trigger
});
</script>

<!-- include Paystack inline lib -->
<script src="https://js.paystack.co/v1/inline.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('checkoutForm');
    const paymentSelect = document.getElementById('payment-method-select');

    form.addEventListener('submit', function (e) {

        if (paymentSelect.value === 'online') {
            e.preventDefault();
            payWithPaystack();
        }
    });

    function payWithPaystack() {

        const amount = parseFloat(document.getElementById('totalAmount').value);

        if (!amount || amount <= 0) {
            alert('Invalid amount.');
            return;
        }

        const handler = PaystackPop.setup({
            key: "{{ env('PAYSTACK_PUBLIC_KEY') }}",
            email: "{{ auth()->user()->email }}",
            amount: amount * 100, // convert to kobo
            currency: "NGN",
            ref: 'PSK_' + Date.now(),

            callback: function (response) {

                fetch("{{ route('checkout.submit') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json", // VERY IMPORTANT
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        paystack_reference: response.reference,
                        payment_method: "online",
                        order_for: document.querySelector('[name="order_for"]').value,
                        member_username: document.querySelector('[name="member_username"]')?.value || null
                    })
                })
                .then(async res => {

                    const text = await res.text();

                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("Server returned HTML instead of JSON:");
                        console.error(text);
                        throw new Error("Server error. Please refresh and try again.");
                    }
                })
                .then(data => {

                    if (data.status === "success") {
                        window.location.href = data.redirect_url;
                    } else {
                        alert(data.message || "Payment verification failed.");
                    }

                })
                .catch(err => {
                    alert("Network error: " + err.message);
                });
            },

            onClose: function () {
                alert('Payment window closed.');
            }
        });

        handler.openIframe();
    }

});
</script>


<script>
document.getElementById('order_for').addEventListener('change', function () {

    let value = this.value;
    let paymentSection = document.getElementById('payment_section');
    let memberBox = document.getElementById('member_username_box');

    if (value === 'self') {
        paymentSection.classList.remove('d-none');
        memberBox.classList.add('d-none');
    } 
    else if (value === 'member') {
        paymentSection.classList.remove('d-none');
        memberBox.classList.remove('d-none');
    } 
    else {
        paymentSection.classList.add('d-none');
        memberBox.classList.add('d-none');
    }
});
</script>

@endsection
