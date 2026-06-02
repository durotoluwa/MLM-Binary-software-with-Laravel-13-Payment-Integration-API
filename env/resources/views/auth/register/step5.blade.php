@extends('layouts.app')

@section('content')
<div class="animated-bg">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header text-center">
                        <a href="#"><img class="logo-auth" src="{{ asset('images/logo.png') }}" alt=""></a>
                        <h4 class="card-title mt-3">Step 5: Security</h4>
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
<form method="POST" action="{{ route('register.post', 5) }}">
    @csrf

    <div class="row">
      <div class="col-12 mb-2">
        <div class="form-group mb-3">
          <label class="form-label required">Username</label>
          <input type="text" name="username" class="form-control" value="{{ old('username', $data['username'] ?? '') }}"  required>
          
        </div>
      </div>
      <div class="col-6 mb-2" hidden>
        <div class="form-group mb-3">
          <label class="form-label required">Password</label>
          <input type="password" name="password" class="form-control" value="secret">
        </div>
      </div>
      <div class="col-6 mb-2" hidden>
        <div class="form-group mb-3">
          <label class="form-label required">Confirm Password</label>
          <input type="password" name="password_confirmation" class="form-control" value="secret">
        </div>
      </div>
    </div>

    <a href="{{ route('register.step', 4) }}" class="btn btn-primary">Back</a>
    
    <button type="submit" class="btn btn-primary">Register</button>
</form>
  <div class="new-account mt-3 text-center">
                                <p>I have an account Already?
                                    <a class="text-primary" href="{{ route('login') }}">Sign In</a>
                                </p>
                            </div>
        </div></div></div>  </div></div></div>
@endsection
