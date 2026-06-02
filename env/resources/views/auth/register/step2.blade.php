@extends('layouts.app')

@section('content')
<div class="animated-bg">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header text-center">
                        <a href="#"><img class="logo-auth" src="{{ asset('images/logo.png') }}" alt=""></a>
                        <h4 class="card-title mt-3">Step 2: Membership</h4>
                    </div>

                    <div class="card-body">

<form method="POST" action="{{ route('register.post', 2) }}">
    @csrf
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
<div id="upline_group"> 
  
    <label for="upline_username">Upline Username</label>
    <input type="text" name="upline_username"
        id="upline_username"
        value="{{ old('upline_username', $data['upline_username'] ?? '') }}"
        class="form-control"  >

    <small id="upline_info" class="text-success" style=" padding:5px; border:solid 1px #F0F0F0; line-height:60px; font-size:17px; font-weight:600;"></small>
    <span id="upline_error" class="text-danger " style=" padding:5px; border:solid 1px #F0F0F0; line-height:60px; font-size:17px; font-weight:600;"></span>
</div>


<div class="form-group mb-3" id="sponsor_group">

  <div id="sponsor_error" style="color: red;"></div>

  <label class="form-label required">Sponsor Username</label>
  <input type="text" id="sponsor_username" name="sponsor_username" class="form-control">
   
</div>

</div>


 

      <div class="col-lg-6 mb-2">
    <a href="{{ route('register.step', 1) }}" class="btn btn-primary">Back</a>
    
    <button type="submit" class="btn btn-primary">Next</button>
    
      </div>
        <div class="new-account mt-3 text-center">
                                <p>I have an account Already?
                                    <a class="text-primary" href="{{ route('login') }}">Sign In</a>
                                </p>
                            </div>
</form>

        </div></div></div>  </div></div></div>
@endsection

