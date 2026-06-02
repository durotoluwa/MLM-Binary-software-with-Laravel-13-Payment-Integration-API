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
   <div class="page-titles">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
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
<form id="checkoutForm" action="{{ route('admincheckoutapprove.submit') }}" method="POST" enctype="multipart/form-data">
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

@if(auth()->user()->hasRole('superadmin'))
<div class="mb-3">
    <label for="user_id" class="form-label">Select User to Order For</label>
    <select name="user_id" id="user_id" class="form-control" required>
        <option value="">-- Select User --</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->first_name }} {{ $user->last_name }})</option>
        @endforeach
    </select>
</div>

<input type="hidden" name="is_admin_order" value="1">
@endif

<input type="hidden" id="totalAmount" value="{{ $total }}">
    <button type="submit" id="confirmPayBtn" class="btn btn-primary">Submit</button>
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
        const selectedMethod = paymentSelect.value;
        if (selectedMethod === 'online') {
            e.preventDefault();
            payWithPaystack();
        }
    });

    function payWithPaystack() {
        const amount = parseFloat(document.getElementById('totalAmount').value);
        const handler = PaystackPop.setup({
            key: '{{ env("PAYSTACK_PUBLIC_KEY") }}',
            email: '{{ auth()->user()->email }}',
            amount: amount * 100,
            currency: "NGN",
            ref: 'PSK_' + Math.floor((Math.random() * 1000000000) + 1),
            callback: function (response) {
                fetch("{{ route('checkout.submit') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        paystack_reference: response.reference,
                        payment_method: 'online'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = data.redirect_url;
                    } else {
                        alert(data.message || 'Payment verification failed.');
                    }
                })
                .catch(err => alert('Network error: ' + err.message));
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
