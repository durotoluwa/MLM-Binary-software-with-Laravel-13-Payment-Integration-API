@extends('layouts.app')

@section('content')
<div class="animated-bg">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header text-center">
                        <a href="#"><img class="logo-auth" src="{{ asset('images/logo.png') }}" alt=""></a>
                        <h4 class="card-title mt-3">Step 3: Next Of Kin - (Optional) </h4>
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
<form method="POST" action="{{ route('register.post', 3) }}">
    @csrf

<div class="row">
      <div class="col-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label ">Full Name</label>
          <input type="text" name="kin_name" class="form-control" value="{{ old('kin_name', $data['kin_name'] ?? '') }}">
        </div>
      </div>
      <div class="col-6 mb-2">
        <div class="form-group mb-3">
          <label class="form-label ">Phone Number</label>
          <input type="text" name="kin_phone" class="form-control" value="{{ old('kin_phone', $data['kin_phone'] ?? '') }}">
        </div>
      </div>
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label ">Email</label>
          <input type="email" name="kin_email" class="form-control" value="{{ old('kin_email', $data['kin_email'] ?? '') }}">
        </div>
      </div>
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label ">Address</label>
          <input type="text" name="kin_address" class="form-control" value="{{ old('kin_address', $data['kin_address'] ?? '') }}">
        </div>
      </div>
    </div>

    <a href="{{ route('register.step', 2) }}" class="btn btn-primary">Back</a>
    
    <button type="submit" class="btn btn-primary">Next</button>
      <div class="new-account mt-3 text-center">
                                <p>I have an account Already?
                                    <a class="text-primary" href="{{ route('login') }}">Sign In</a>
                                </p>
                            </div>
</form>

        </div></div></div>  </div></div></div>
@endsection
