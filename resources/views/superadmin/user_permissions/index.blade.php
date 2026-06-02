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
            <a href="">Assign Permissions to User</a>
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
<div class="card h-auto">
<div class="card-body">

 <form method="GET" action="{{ route('user_permissions.index') }}">
        <div class="mb-3">
            <label>Select User</label>
            <select name="user_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Choose User --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    <!-- If User Selected -->
    @if($selectedUser)
        <form method="POST" action="{{ route('user_permissions.store') }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">

            <div class="mb-3">
                <label>Assign Permissions</label>
                <div class="row">
                    @foreach($permissions as $permission)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                    class="form-check-input" id="perm_{{ $permission->id }}"
                                    {{ $selectedUser->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Update Permissions</button>
        </form>
    @endif
          
</div>
</div></div></div></div><!-----end of row----->
    



  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
