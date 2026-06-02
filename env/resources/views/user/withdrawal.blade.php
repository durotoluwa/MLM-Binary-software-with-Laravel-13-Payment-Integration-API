@extends('layouts.app')
@section('content')


@include('layouts.navbar')
 

        <!--******** Header start **********-->
   @include('user.headertop')      
 
        <!--******* Header end *************-->

        <!--******* Sidebar start **********-->
     
@include('user.sidebar')     

        <!--******** Sidebar end ***********-->

        <!-- Container starts-->
        <div class="content-body">
  <!-- row -->
  <div class="container-fluid">
   <div class="page-titles">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('user.dashboard') }}">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">
          <a href="">Withdraw From Wallet</a>
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

    <div class="row">
 <div class="col-xl-12">
<div class="card">
<div class="card-body">
  
  <form action="{{ route('user.withdraw') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="form-group mb-5">
      <label style="color:black; font-weight:500; font-size:13px;">Wallet Balance:</label>
        <input type="text" required class="form-control" readonly value="₦{{ number_format(auth()->user()->withdraw_wallet_balance ?? 0, 2) }}">
    </div>
    <div class="row mb-5">
      <div class="col-md-4">
         <label style="color:black; font-weight:500; font-size:13px;">Bank Name:</label>
        <input type="text" required class="form-control" readonly value="{{ Auth::user()->bank_name }}">
      </div>
      <div class="col-md-4">
         <label style="color:black; font-weight:500; font-size:13px;">Account Number:</label>
        <input type="text" required class="form-control" readonly value="{{ Auth::user()->account_no }}">
      </div>
      <div class="col-md-4">
         <label style="color:black; font-weight:500; font-size:13px;">Account Name:</label> 
        <input type="text" required class="form-control" readonly value="{{ Auth::user()->account_name }}">
      </div>
    </div>


        <div class="form-group mb-4">
      <label style="color:black; font-weight:500; font-size:13px;">Enter Amount:</label>
        <input type="number" name="amount" required class="form-control" placeholder="Enter the amount you want to withdraw" min="100" max="{{ auth()->user()->withdraw_wallet_balance ?? 0 }}">
    </div>

       <div class="form-group">
      <label style="color:black; font-weight:500; font-size:13px;">Transaction Pin:</label>
        <input type="text" name="transaction_pin" required class="form-control" placeholder="Enter your transaction pin">
    </div>

  

    <button type="submit" class="btn btn-primary mt-3">Withdraw Now</button>
</form>

 
  </div>
</div> </div> </div>

        <!-- Container Ends-->

@include('layouts.footer_content')

<script>
document.getElementById('topup-method').addEventListener('change', function () {
    const bankFields = document.getElementById('bank-fields');
    bankFields.style.display = this.value === 'bank' ? 'block' : 'none';
});
</script>

@endsection
