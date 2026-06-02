@extends('layouts.app')
@section('content')

 

    <div class="fix-wrapper">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-md-5">
                    <div class="form-input-content text-center error-page">
                        <h1 class="error-text font-weight-bold">400</h1>
                        <h4><i class="fa fa-thumbs-down text-danger"></i> Bad Request</h4>
                        <p>Your Request resulted in an error</p>
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
