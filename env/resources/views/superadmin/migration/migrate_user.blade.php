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
            <a href=""> Migration</a>
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

 
<div class="container">
    <h3>Superadmin — User Migration / Manual Entry</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger"><ul>@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('superadmin.migrate.store') }}">
        @csrf

        <div class="card mb-3 p-3">
            <h5>1. Pick existing user OR create new</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Existing user (search)</label>
                    <select id="existing_user_id" name="existing_user_id" class="form-control"></select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Or create new — Username</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}">
                </div>


                    <div class="col-md-6 mb-3">
                    <label>Full name</label>
                    <div class="input-group">
                        <input type="text" name="first_name" placeholder="First" class="form-control" value="{{ old('first_name') }}">
                        <input type="text" name="last_name" placeholder="Last" class="form-control" value="{{ old('last_name') }}">
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>

                   <div class="col-md-6 mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>

                   <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>

            
            </div>
        </div>

        <div class="card mb-3 p-3">
            <h5>2. Genealogy — binary & sponsor</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Upline (username)</label>
                    <input type="text" id="upline_username" name="upline_username" class="form-control" value="{{ old('upline_username') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Sponsor (username)</label>
                    <input type="text" id="sponsor_username" name="sponsor_username" class="form-control" value="{{ old('sponsor_username') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Position</label>
                    <select name="position" class="form-control">
                        <option value="">-- Leave blank to auto</option>
                        <option value="left">Left</option>
                        <option value="right">Right</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card mb-3 p-3">
            <h5>3. Package (optional)</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Package</label>
                    <select name="package_id" class="form-control">
                        <option value="">-- No package --</option>
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}">{{ $pkg->packageName }} — ₦{{ number_format($pkg->price,2) }} — {{ $pkg->bottle }} bottles</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Amount Paid</label>
                    <input type="text" name="amount_paid" class="form-control" value="{{ old('amount_paid') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>CTP to record</label>
                    <input type="number" name="ctp_received" class="form-control" value="{{ old('ctp_received') }}">
                </div>
            </div>
        </div>

        <div class="card mb-3 p-3">
            <h5>4. Wallets & Matching carryover</h5>
            <div class="row">
                <div class="col-md-3 mb-3"><label>Deposit wallet</label><input type="text" name="deposit_wallet_balance" class="form-control"></div>
 
                <div class="col-md-3 mb-3"><label>Unilevel wallet</label><input type="text" name="unilevel_wallet_balance" class="form-control"></div>
                <div class="col-md-3 mb-3"><label>Withdraw wallet</label><input type="text" name="withdraw_wallet_balance" class="form-control"></div>

                <div class="col-md-3 mb-3"><label>Left CPT (carry)</label><input type="number" name="left_ctp_for_matching" class="form-control"></div>
                <div class="col-md-3 mb-3"><label>Right CPT (carry)</label><input type="number" name="right_ctp_for_matching" class="form-control"></div>
            </div>
        </div>

        <div class="card mb-3 p-3">
            <h5>5. Misc</h5>
            <div class="row">
                <div class="col-md-3 mb-3"><label>Status</label>
                    <select name="status" class="form-control"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div class="col-md-3 mb-3"><label>Payment status</label>
                    <select name="payment_status" class="form-control"><option value="approved">Approved</option><option value="pending">Pending</option></select></div>
            </div>
        </div>

        <button class="btn btn-primary">Save Migration Record</button>
    </form>
</div>

 

 
</div></div></div></div><!-----end of row----->
    



  </div>
</div>

 <!-- Mute User Modal -->
<div class="modal fade" id="muteUserModal" tabindex="-1" aria-labelledby="muteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="muteUserForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Mute Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Mute status for: <strong id="muteUserName"></strong></p>
                    <select name="is_muted" class="form-select" id="muteSelect">
                        <option value="1">Mute</option>
                        <option value="0">Unmute</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

  

@include('layouts.footer_content')



<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function(){
    // Select2 for existing user search
    $('#existing_user_id').select2({
        placeholder: '-- search user --',
        ajax: {
            url: '{{ route('superadmin.user.search') }}',
            dataType: 'json',
            delay: 250,
            data: function(params){ return { q: params.term }; },
            processResults: function(data){ return { results: data.results }; }
        },
        minimumInputLength: 1,
    });

    // when user selected, fetch their last package
    $('#existing_user_id').on('change', function(){
        var id = $(this).val();
        if (!id) return;
        $.getJSON('/superadmin/user/' + id + '/last-package', function(res){
            if (res.status === 'success' && res.last_package) {
                alert('Last package: ' + res.last_package.name + ' — ' + res.last_package.date);
            }
        });
    });
});
</script>
 

@endsection
