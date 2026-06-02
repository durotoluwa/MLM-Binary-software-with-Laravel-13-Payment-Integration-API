@extends('layouts.app')

@section('content')
<div class="animated-bg">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header text-center">
                        <a href="#"><img class="logo-auth" src="{{ asset('images/logo.png') }}" alt=""></a>
                        <h4 class="card-title mt-3">Step 4: Bank Details</h4>
                    </div>

                    <div class="card-body">
@if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
<form method="POST" action="{{ route('register.post', 4) }}">
    @csrf

    <div class="form-group mb-3">
        <label class="form-label required">Bank Name</label>
        <select name="bank_code" id="bank_code" class="form-control" required>
            <option value="">-- Select Bank --</option>
            @foreach($banks as $bank)
                <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}">
                    {{ $bank['name'] }}
                </option>
            @endforeach
        </select>
        <input type="hidden" name="bank_name" id="bank_name">
    </div>

    <div class="form-group mb-3">
        <label class="form-label required">Account Number</label>
        <input type="text" name="account_no" id="account_no" class="form-control" required>
    </div>

    <div class="form-group mb-3">
        <label class="form-label required">Account Name</label>
        <input type="text" name="account_name" id="account_name" class="form-control" required>
    </div>

    <!-- Hidden field for recipient code -->
    <input type="hidden" name="paystack_recipient_code" id="paystack_recipient_code">

    <a href="{{ route('register.step', 3) }}" class="btn btn-primary">Back</a>
    <button type="submit" class="btn btn-primary">Next</button>
</form>


 

<script>
document.addEventListener("DOMContentLoaded", function () {
    const bankSelect = document.querySelector('select[name="bank_code"]');
    const bankNameInput = document.getElementById('bank_name');
    const accountNoInput = document.getElementById('account_no');
    const accountNameInput = document.getElementById('account_name');
    const recipientCodeInput = document.createElement('input');

    // Hidden field for recipient code
    recipientCodeInput.type = 'hidden';
    recipientCodeInput.name = 'paystack_recipient_code';
    recipientCodeInput.id = 'paystack_recipient_code';
    document.querySelector('form').appendChild(recipientCodeInput);

    bankSelect.addEventListener('change', function () {
        const selectedOption = bankSelect.options[bankSelect.selectedIndex];
        bankNameInput.value = selectedOption.getAttribute('data-name') || '';

        const bankCode = bankSelect.value;
        const accountNo = accountNoInput.value;
        const accountName = accountNameInput.value;

        // Only call backend if all fields are filled
        if (bankCode && accountNo && accountName) {
            fetch('/register/recipient', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    bank_code: bankCode,
                    bank_name: bankNameInput.value,
                    account_no: accountNo,
                    account_name: accountName
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.recipient_code) {
                    recipientCodeInput.value = data.recipient_code;
                } else {
                    alert('Unable to generate recipient code');
                }
            })
            .catch(() => alert('Error contacting server'));
        }
    });
});
</script>



  <div class="new-account mt-3 text-center">
                                <p>I have an account Already?
                                    <a class="text-primary" href="{{ route('login') }}">Sign In</a>
                                </p>
                            </div>
        </div></div></div>  </div></div></div>
@endsection
