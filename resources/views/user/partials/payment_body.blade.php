<div class="col-xl-6">
    <div class="card">
        <div class="card-header">
            <h4 class="heading mb-0">Payment With Transfer</h4>
        </div>
        <div class="card-body">
            <div class="alert alert-warning border-warning outline-dashed py-3 px-3 mt-1 mb-4 mb-0 text-dark d-flex align-items-center">
                <div class="mx-3">
                    <h6 class="mb-0 fw-semibold">We need your attention!</h6>
                    <p class="mb-0">Registration payment needs to be paid before you can be verified as a member of this community.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm-12">
                    <div class="border border-secondary border-opacity-10 rounded p-3">
                        <h6 class="fs-14 mb-2">REGISTRATION FEE</h6>
                        <span class="h6 mb-0">{{ $currencySymbol }}{{ $amount }}</span>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="border border-secondary border-opacity-10 rounded p-3">
                        <h6 class="fs-14">COMPANY ACCOUNT DETAILS</h6>

                        <span class="h6 mb-0">Account Number</span>
                        <p class="fs-13 mb-2">{{ $accountNumber }}</p>

                        <span class="h6 mb-0">Account Name</span>
                        <p class="fs-13 mb-2">{{ $accountName }}</p>

                        <span class="h6 mb-0">Bank Name</span>
                        <p class="fs-13 mb-2">{{ $bankName }}</p>

                        <a data-bs-toggle="modal" data-bs-target="#exampleModalCenter" class="btn btn-primary" style="margin-bottom: 20px;">
                            <i class="fa-solid fa-list"></i> Upload Proof Of Payment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Payment With Card --}}
<div class="col-xl-6">
    <div class="card">
        <div class="card-header">
            <h4 class="heading mb-0">Payment With Card</h4>
        </div>
        <div class="card-body">
            <div class="alert alert-warning border-warning outline-dashed py-3 px-3 mt-1 mb-4 mb-0 text-dark d-flex align-items-center">
                <div class="mx-3">
                    <h6 class="mb-0 fw-semibold">We need your attention!</h6>
                    <p class="mb-0">Registration payment needs to be paid before you can be verified as a member of this community.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm-12">
                    <div class="border border-secondary border-opacity-10 rounded p-3">
                        <h6 class="fs-14 mb-2">REGISTRATION FEE</h6>
                        <span class="h6 mb-0">{{ $currencySymbol }}{{ $amount }}</span>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="border border-secondary border-opacity-10 rounded p-3">
                        <h6 class="fs-14">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h6>
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ asset('images/card1.jpg') }}" class="me-2" alt="">
                            <div>
                                <h6 class="fs-13 mb-1">Visa **** 1679</h6>
                                <p class="fs-13 mb-0">Card expires at 00/00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="border border-secondary border-opacity-10 rounded p-3">
                        <h6 class="fs-14">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</h6>
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ asset('images/card2.jpg') }}" class="me-2" alt="">
                            <div>
                                <h6 class="fs-13 mb-1">Mastercard **** 2704</h6>
                                <p class="fs-13 mb-0">Card expires at 00/00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 mt-20">
@php
    $amount = setting('registration_fee', 5000);
    $user = Auth::user();
@endphp

<div class="text-center mt-3">
    <h5>Registration Payment</h5>
    <p>Amount to Pay: <strong>₦{{ number_format($amount, 2) }}</strong></p>

    <button class="btn btn-primary" id="paystackButton">
        Pay Online (Paystack)
    </button>
</div>


<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('paystackButton').addEventListener('click', function() {
    let handler = PaystackPop.setup({
        key: '{{ env('PAYSTACK_PUBLIC_KEY') }}', // your Paystack public key
        email: '{{ $user->email }}',
        amount: {{ $amount * 100 }}, // in kobo
        currency: 'NGN',
        ref: '{{ \Illuminate\Support\Str::uuid() }}', // unique reference
        callback: function(response) {
            // Redirect to verify route after successful payment
            window.location.href = "{{ route('payment.paystack.verify') }}?reference=" + response.reference;
        },
        onClose: function() {
            alert('Payment window closed.');
        }
    });
    handler.openIframe();
});
</script>


                </div>
            </div>
        </div>
    </div>
</div>
