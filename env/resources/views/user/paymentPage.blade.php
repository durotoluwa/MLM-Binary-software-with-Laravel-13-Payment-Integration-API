@extends('layouts.app')
@section('content')


@include('layouts.navbar')
 

        <!--******** Header start **********-->
   @include('user.headertop')      
 
        <!--******* Header end *************-->

        <!--******* Sidebar start **********-->
     
@include('user.sidebar2')     

        <!--******** Sidebar end ***********-->

        <!-- Container starts-->
        <div class="content-body">
  <!-- row -->
  <div class="container-fluid">
    <div class="row">

<!--=====================  Page Title Start Here =====================-->
    <div class="page-titles">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href=" ">Dashboard</a>
          </li>
          <li class="breadcrumb-item active">
            <a href="">Member Registration Page</a>
          </li>
        </ol>
      </div>
 
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
 
   <div class="tab-content" id="tabContentMyProfileBottom">
<div class="row">


    @php
    $isNigeria = strtolower($userCountry) === 'nigeria';
@endphp

<div class="tab-content" id="tabContentMyProfileBottom">
    <div class="row">

        {{-- Load Payment Body --}}
        @include('user.partials.payment_body', [
            'currencySymbol' => $isNigeria ? '₦' : '$',
            'amount' => $isNigeria ? number_format($nairaFee, 2) : number_format($usdFee, 2),
            'accountNumber' => $isNigeria ? '2334546546' : '1122334455', // Change USD account no
            'accountName' => 'Divine Leverage Team Limited',
            'bankName' => $isNigeria ? 'FCMB' : 'Bank of America'
        ])

    </div>
</div>

{{-- Modal for proof of payment --}}
@include('user.partials.payment_modal')





                </div>
              </div>



        <!-- Container Ends-->

@include('layouts.footer_content')




@endsection
