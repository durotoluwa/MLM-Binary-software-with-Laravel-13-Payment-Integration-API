@extends('layouts.app')

@section('content')
<div class="animated-bg">
    <div class="fix-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-6">
                    <div class="card mb-0 h-auto">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <a href="#"><img class="logo-auth" src="{{ asset('images/logo.png') }}" alt=""></a>
                            </div>

                            <h4 class="text-center mb-4">Sign in to your account</h4>

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

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <strong>Error:</strong> {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" class="mx-auto" style="max-width: 400px;">
                                @csrf

                                <div class="form-group mb-4">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}" required autofocus>
                                    @error('username') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-sm-4 mb-3 position-relative">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" name="remember" id="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                                    <label for="remember" class="form-check-label">Remember me</label>
                                </div>

                                <div class="form-row d-flex flex-wrap justify-content-between mb-2">
                                    <div class="form-group ms-2">
                                        <a class="text-hover" href="{{ route('password.request') }}">Forgot Password?</a>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                                </div>
                            </form>

                            <div class="new-account mt-3 text-center">
                                <p>Don't have an account? 
                                    <a class="text-primary" href="{{ route('register') }}">Sign up</a>
                                </p>
                            </div>
                        </div> {{-- card-body --}}
                    </div> {{-- card --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
