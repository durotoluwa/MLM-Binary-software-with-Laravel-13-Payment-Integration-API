@extends('layouts.app')
@section('content')

 


   <div class="fix-wrapper">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-md-5">
                    <div class="form-input-content text-center error-page">
                        <h1 class="error-text  font-weight-bold">419</h1>
                        <h4><i class="fa fa-times-circle text-danger"></i>Page Expired </h4>
                        <p> CSRF token mismatch/expired session.</p>
						<div>
                            <a class="btn btn-primary" href="{{ route('login') }}">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



@include('layouts.footer_content')

 


@endsection
