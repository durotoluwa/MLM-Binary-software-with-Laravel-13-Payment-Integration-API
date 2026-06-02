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
            <a href=""> All In-Active Members</a>
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


<div class="table-responsive">
        
         <table id="responsiveTable" class="display responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>S/N</th>
                 
                <th>Name</th>
                <th>Username</th>
                <th>Status</th>
                <th>Action</th>
                  
                 
                  </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                 @foreach($users as $index => $user)
                  <tr>
                       <td>{{ $no }}</td>

                         <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                    <td>{{ $user->username }}</td>
                    <td>
                        @if($user->is_muted)
                            <span class="badge bg-danger">Muted</span>
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </td>
             <td>
@can('mute members')
<button type="button"
        class="btn btn-sm btn-warning"
        data-bs-toggle="modal"
        data-bs-target="#muteUserModal"
        data-user-id="{{ $user->id }}"
        data-user-name="{{ $user->name }}"
        data-is-muted="{{ $user->is_muted }}">
    Mute Settings
</button>
@endcan


                </td>
       
 
                  </tr>

                  
             
    <?php $no++; ?>
 @endforeach
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
