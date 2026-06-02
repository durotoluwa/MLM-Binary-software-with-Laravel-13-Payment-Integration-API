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
          <a href="">Purchase Package for member - {{ $user->first_name }} {{ $user->last_name }} - ({{ $user->username }})</a>
        </li>
      </ol>
    </div>
 <div class="row">
    
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




 <div class="col-xl-12">
<div class="card">
<div class="card-body">
<div class="row separate-row">
                  <div class="col-sm-4">
                    <div class="job-icon pb-4 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h2 class="mb-0 lh-1">{{ $package->packageName }}</h2>
                         
                        </div>
                        <span class="d-block mb-2">Package Name</span>
                      </div>
                     
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="job-icon pb-4 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h2 class="mb-0 lh-1">₦{{ number_format($upgradePrice, 2) }}</h2>
                        </div>
                        <span class="d-block mb-2">
                          Package Price: ₦{{ number_format($package->price, 2) }}
                        </span>
                        @if($isUpgrade)
                          <small class="text-success">
                            Upgrade applied (₦{{ number_format($lastPackagePrice, 2) }} deducted).
                          </small>
                        @endif
                      </div>
                    </div>
                  </div>
                  


                  <div class="col-sm-4">
                    <div class="job-icon pb-4 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h2 class="mb-0 lh-1">
                            ₦{{ number_format($package->apc, 2) }} × {{ $bottleCount }} =
                            ₦{{ number_format($isUpgrade ? $currentApcTotal : $apcTotal, 2) }}
                          </h2>
                        </div>
                        <span class="d-block mb-2">
                          @if($isUpgrade) Current APC @else Main APC @endif
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  




                  <div class="col-sm-4">
                    <div class="job-icon pt-4 pb-sm-0 pb-4 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h2 class="mb-0 lh-1">
                            ₦{{ number_format($finalPrice, 2) }}
                          </h2>
                        </div>
                        <span class="d-block mb-2">Total Payment</span>
                      </div>
                    </div>
                  </div>
                  


                  <div class="col-sm-4">
                    <div class="job-icon pt-4 pb-sm-0 pb-4 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h2 class="mb-0 lh-1" id="package-cpt">
                            @if($isUpgrade)
                              {{ $currentCpt }}
                            @else
                              {{ $package->cpts }}
                            @endif
                          </h2>
                        </div>
                        <span class="d-block mb-2">
                          @if($isUpgrade) Current CPT @else Package CPT @endif
                        </span>
                      </div>
                    </div>
                  </div>
                  



                  <div class="col-sm-4">
                    <div class="job-icon pt-4  d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h2 class="mb-0 lh-1">₦{{ number_format($user->deposit_wallet_balance, 2) }}</h2>
                        </div>
                        <span class="d-block mb-2">Current Wallet Balance</span>
                      </div>
                     
                    </div>
                  </div></div>













</div></div>
 </div><!--========end col==========-->
    </div><!--========end row==========-->


  

 <div class="row">
 <div class="col-xl-12">
<div class="card">
<div class="card-body">
<form id="purchaseForm" action="{{ route('user.package.memberpurchase') }}" method="POST" enctype="multipart/form-data">
    @csrf
 
<input type="hidden" id="member_id" name="member_id" value="{{ $user->id }}">



<div class="row">
 <div class="col-xl-12">
<div class="card">
<div class="card-body">
 
<table class="table">
<thead>
<tr>
<th>Product</th>
<th>Product Image</th>
<th>Product CPT</th>
<th>Qty</th>
</tr>
</thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>
                        {{ $product->productName }}
                        <input type="hidden" name="product[{{ $loop->index }}][id]" value="{{ $product->id }}">
                    </td>
                    <td>
                        @if($product->product_image)
                            <img src="{{ asset($product->product_image) }}" alt="{{ $product->productName }}" style="width: 50px; height: 50px;">
                        @else
                            <span>No Image</span>
                        @endif
                    </td>
                       <td>
                        {{ $product->cpts }}</td>
                    
                  
                    <td>
                        <input type="number" name="product[{{ $loop->index }}][qty]" class="form-control" value="0" min="0">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    



</div></div>
 </div></div>









 

 <input type="hidden" name="amount" id="amount" value="{{ $finalPrice }}">



    <div class="form-group mt-3">
      <label>Select Payment Method:</label>
      <select name="payment_method" class="form-control" required id="payment-method-select">
        <option value="">-- Select --</option>
        <option value="wallet">Deposit Wallet</option>
        <option value="bank">Bank Transfer</option>
        <option value="online">Online Payment (Paystack)</option>
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
  <input type="text" name="amount" class="form-control" >
<input type="hidden" name="total_amount_package" value="{{ number_format($finalPrice, 2) }}">
 
</div>

<div id="proof-upload" style="display:none;" class="mb-3"> <!-- Example -->
  <label>Proof of Payment</label>
  <input type="file" name="payment_proof" class="form-control">
</div>

 

<div id="wallet-pin-group" style="display: none; margin-top:40px;">
  <label for="transaction_pin">Transaction PIN</label>
  <input type="text" name="transaction_pin" class="form-control" placeholder="Enter your PIN">
</div>


<input type="hidden" id="package_id" name="package_id" value="{{ $package->id }}">

<!--
 <input type="hidden"  name="packagepurchase_id" value="{{ $package->id }}">-->

<button type="submit" class="btn btn-primary mt-3" id="pay-now-btn">Confirm Purchase</button>

 
  </form>


</div></div>
 </div></div>



 
  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')

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
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('pay-now-btn').addEventListener('click', function (e) {
    const paymentMethod = document.getElementById('payment-method-select').value;
    if (paymentMethod !== 'online') return; // Let Laravel handle others

    e.preventDefault();

    const amount = document.getElementById('amount').value;
    const email = '{{ auth()->user()->email }}';
    const reference = 'pkg_' + Math.floor((Math.random() * 1000000000) + 1);
    const packageId = document.getElementById('package_id').value;
    const memberId = document.getElementById('member_id').value; // ✅ target user

    const rows = document.querySelectorAll('tbody tr');
    const product = Array.from(rows).map(row => {
        const id = row.querySelector('input[type="hidden"]').value;
        const qty = parseInt(row.querySelector('input[type="number"]').value) || 0;
        return { id, qty };
    }).filter(item => item.qty > 0);

    const handler = PaystackPop.setup({
        key: '{{ env("PAYSTACK_PUBLIC_KEY") }}',
        email: email,
        amount: amount * 100,
        currency: 'NGN',
        ref: reference,

        callback: function (response) {
            fetch('{{ route("user.package.memberpurchase") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    payment_method: 'online',
                    paystack_reference: response.reference,
                    package_id: packageId,
                    member_id: memberId,   // ✅ include member_id
                    product: product
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    toastr.success(data.message);
                    window.location.href = data.redirect_url;
                } else {
                    toastr.error(data.message || 'Payment verification failed');
                    console.error(data);
                }
            })
            .catch(err => {
                toastr.error('Network error occurred');
                console.error('Fetch error:', err);
            });
        },

        onClose: function () {
            toastr.info('Transaction was not completed.');
        }
    });

    handler.openIframe();
});
</script>













 




<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInputs = document.querySelectorAll('input[name^="product"][name$="[qty]"]');
    const cptCells = document.querySelectorAll('td:nth-child(3)');
    const confirmBtn = document.getElementById('pay-now-btn');
    const packageCpt = parseFloat(document.getElementById('package-cpt').textContent);

    function updateButtonVisibility() {
        let totalCpt = 0;

        qtyInputs.forEach((input, index) => {
            const qty = parseInt(input.value) || 0;
            const cpt = parseFloat(cptCells[index].textContent) || 0;
            totalCpt += qty * cpt;
        });

        if (totalCpt === packageCpt) {
            confirmBtn.style.display = 'block';
        } else {
            confirmBtn.style.display = 'none';
        }
    }

    qtyInputs.forEach(input => {
        input.addEventListener('input', updateButtonVisibility);
    });

    updateButtonVisibility(); // Initial check
    
});
</script>



@endsection
