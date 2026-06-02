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

    <div class="row">
        <div class="col-12 mb-2">
            <div class="form-group mb-3">
                <label class="form-label required">Bank Name</label>
                <select name="bank_code" class="form-control" required>
                    <option value="">-- Select Bank --</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank['code'] }}" data-name="{{ $bank['name'] }}"
                            {{ old('bank_code', $data['bank_code'] ?? '') == $bank['code'] ? 'selected' : '' }}>
                            {{ $bank['name'] }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="bank_name" id="bank_name" value="{{ old('bank_name', $data['bank_name'] ?? '') }}">
            </div>
        </div>

        <div class="col-12 mb-2">
            <div class="form-group mb-3">
                <label class="form-label required">Account Number</label>
                <input type="text" name="account_no" class="form-control" value="{{ old('account_no', $data['account_no'] ?? '') }}" required>
            </div>
        </div>

<div class="col-12 mb-2">
    <div class="form-group mb-3">
        <label class="form-label required">Account Name</label>
        <input type="text" name="account_name"   class="form-control" 
               value="{{ old('account_name', $data['account_name'] ?? '') }}" required>
    </div>
</div>



    </div>

    <a href="{{ route('register.step', 3) }}" class="btn btn-primary">Back</a>
    <button type="submit" class="btn btn-primary">Next</button>
</form>

<script>
document.querySelector('select[name="bank_code"]').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('bank_name').value = selectedOption.getAttribute('data-name');
});
</script>

  <div class="new-account mt-3 text-center">
                                <p>I have an account Already?
                                    <a class="text-primary" href="{{ route('login') }}">Sign In</a>
                                </p>
                            </div>
        </div></div></div>  </div></div></div>
@endsection
