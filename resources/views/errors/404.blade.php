@extends('layouts.app')
@section('content')

 


 


        <div class="fix-wrapper">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-md-6">
                    <div class="form-input-content text-center error-page">
                        <h1 class="error-text font-weight-bold">404</h1>
                        <h4><i class="fa fa-exclamation-triangle text-warning"></i> The page you were looking for is not found!</h4>
                        <p>You may have mistyped the address or the page may have moved.</p>
						<div>
                           <a class="btn btn-primary" href="#" onclick="history.back(); return false;">Go back</a>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@include('layouts.footer_content')

 


@endsection
