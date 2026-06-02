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
            <a href=""> Incentive Settings </a>
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
 
  <a href="{{ route('incentive_settings.create') }}" class="btn btn-primary mb-3">+ Add Incentive</a>


<div class="row">
<div class="col-xl-12">
<div class="card h-auto">
<div class="card-body">


<div class="table-responsive">
        
         <table id="responsiveTable" class="display responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>S/N</th>
                 
               <th>Rank</th>
                <th>Required CTP</th>
                <th>Lesser Leg %</th>
                <th>Downline Count</th>
                <th>Downline Rank</th>
                <th>Status</th>
                <th>Actions</th>
              
                  </tr>
                </thead>
                <tbody>
                  @forelse($settings as $setting)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $setting->rank }}</td>
                    <td>{{ $setting->required_ctp }}</td>
                    <td>{{ $setting->min_lesser_leg_percent }}%</td>
                    <td>{{ $setting->required_downline_count }}</td>
                    <td>{{ $setting->required_downline_rank }}</td>
                    <td>{{ $setting->is_active ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <a href="{{ route('incentive_settings.edit', $setting->id) }}" class="btn btn-sm btn-info">Edit</a>
                        <form action="{{ route('incentive_settings.destroy', $setting->id) }}" method="POST" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this setting?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
             
            @endforelse
</tbody></table>
          

 

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



@endsection
