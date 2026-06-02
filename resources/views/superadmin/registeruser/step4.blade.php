@extends('layouts.app')
@section('content')


@include('layouts.navbar')
 

        <!--******** Header start **********-->
   @include('superadmin.headertop')      
 
        <!--******* Header end *************-->

        <!--******* Sidebar start **********-->
     
@include('superadmin.sidebar')     

        <!--******** Sidebar end ***********-->

        <!-- Container starts-->
        <div class="content-body">
  <!-- row -->
  <div class="container-fluid">
 <!--=====================  Page Title Start Here =====================-->
    <div class="page-titles">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="{{ route('user.dashboard') }}">Dashboard</a>
          </li>
          <li class="breadcrumb-item active">
            <a href=""> Add Members</a>
          </li>
        </ol>


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
<div class="card h-auto">
<div class="card-body">

 <div class="card-header text-center">
                        <h4 class="card-title mt-3">Step 4: Bank Details</h4>
                    </div>
<div class="container py-5">

 
<form method="POST" action="{{ route('superadmin.registeruser.post', 4) }}">
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

    <a href="{{ route('superadmin.registeruser.step', 3) }}" class="btn btn-primary">Back</a>
    <button type="submit" class="btn btn-primary">Next</button>
</form>

<script>
document.querySelector('select[name="bank_code"]').addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('bank_name').value = selectedOption.getAttribute('data-name');
});
</script>
</script>


</div>
 

</div></div></div></div><!-----end of row----->
    



  </div>
</div>

  

  

@include('layouts.footer_content')



@endsection
