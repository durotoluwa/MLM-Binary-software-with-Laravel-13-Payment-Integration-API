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
            <a href=""> Create Incentive  </a>
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
 
  <a href="{{ route('incentive_settings.index') }}" class="btn btn-primary mb-3">+ Incentive List</a>


<div class="row">
<div class="col-xl-12">
<div class="card h-auto">
<div class="card-body">

   <form action="{{ isset($setting) ? route('incentive_settings.update', $setting->id) : route('incentive_settings.store') }}" method="POST">
        @csrf
        @if(isset($setting)) @method('PUT') @endif

        <div class="mb-3">
            <label>Rank</label>
            <input type="text" name="rank" value="{{ old('rank', $setting->rank ?? '') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Required CTP</label>
            <input type="number" step="0.01" name="required_ctp" value="{{ old('required_ctp', $setting->required_ctp ?? '') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Min Lesser Leg Percent</label>
            <input type="number" step="0.01" name="min_lesser_leg_percent" value="{{ old('min_lesser_leg_percent', $setting->min_lesser_leg_percent ?? '') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Required Downline Count</label>
            <input type="number" name="required_downline_count" value="{{ old('required_downline_count', $setting->required_downline_count ?? '') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Required Downline Rank</label>
            <input type="text" name="required_downline_rank" value="{{ old('required_downline_rank', $setting->required_downline_rank ?? '') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="is_active" class="form-control">
                <option value="1" {{ old('is_active', $setting->is_active ?? 1) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $setting->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">{{ isset($setting) ? 'Update' : 'Create' }}</button>
    </form>







 
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



@endsection
