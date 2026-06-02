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
            <a href="">Permissions</a>
          </li>
        </ol>
      </div>
<a data-bs-toggle="modal" data-bs-target="#exampleModalCenter" class="btn btn-primary" style="margin-bottom: 20px;"><i class="fa-solid fa-list"></i> Create Permission</a>
 
 

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
       <table class="table table-responsive-md">
                <thead>
                  <tr>

                    <th>Name</th>
                    <th> Action</th>
                  </tr>
                </thead>
                <tbody>
                      @foreach($permissions as $permission)
                  <tr>
                     
                   <td>{{ $permission->name }}</td>
             
                    <td>
<button data-bs-toggle="modal" data-bs-target="#exampleModalCenter{{ $permission->id }}" type="button" class="btn btn-rounded btn-outline-primary"><i class="fa-solid fa-pen-to-square"></i> Edit Permission</button>
<button data-bs-toggle="modal" data-bs-target="#basicModal{{ $permission->id }}"  type="button" class="btn btn-rounded btn-outline-danger"><i class="fa-solid fa-trash"></i> Delete</button>        

       <!-- Edit Permission -->
              <div class="modal fade" id="exampleModalCenter{{ $permission->id }}">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Permission</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                   
    <form action="{{ route('permissions.update', $permission->id) }}" method="POST" enctype="multipart/form-data">
    @csrf   
    @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Permission Name</label>
            <input value="{{ $permission->name }}" type="text" name="name" class="form-control" placeholder="Permission Name" required>
        </div>

                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary">Update Permission</button>
                    </div>
                     </form>
                  </div>
                </div>
              </div>

</td>   

 <!--==== Delete Permission =======-->
            <div class="modal fade" id="basicModal{{$permission->id }}">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Delete permission - {{$permission ->name}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">Are you sure you want to delete {{$permission ->name}} permission , you cant get it back again, once deleted</div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                     
                      <form action="{{ route('permissions.destroy', $permission) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" >Delete</button>
                        </form>

                   

                  </div>
                </div>
              </div>



                  </tr>

                  
             

 @endforeach
</tbody></table>
          

  <!-- create Permission -->
              <div class="modal fade" id="exampleModalCenter">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Create Permission</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                   
    <form method="POST" action="{{ route('permissions.store') }}">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Permission Name</label>
            <input type="text" name="name" class="form-control" placeholder="Permission Name" required>
        </div>

                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary">Save Permission</button>
                    </div>
                     </form>
                  </div>
                </div>
              </div>

</div>
</div></div></div></div><!-----end of row----->
    



  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
