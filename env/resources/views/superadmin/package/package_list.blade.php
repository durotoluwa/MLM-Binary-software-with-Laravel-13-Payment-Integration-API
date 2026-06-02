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
            <a href=""> All Package</a>
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
 
<a  href="{{ route('superadmin.package.create_package') }}" class="btn btn-primary" style="margin-bottom: 20px;"><i class="fa-solid fa-list"></i> Create Package</a>
 
 
<div class="row">
<div class="col-xl-12">
<div class="card h-auto">
<div class="card-body">


<div class="table-responsive">
        
         <table id="responsiveTable" class="display responsive nowrap w-100">
                <thead>
                  <tr>
                    <th>S/N</th>
                 
                <th>Package Name</th>
                <th>Price</th>
                 <th>Image</th>
                <th>CPTS</th>
                <th>Bottle</th>
                <th>Status</th>
                <th>Action</th>
                  
                 
                  </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                 @foreach ($packageList as $data)
                  <tr>
                       <td>{{ $no }}</td>

                         <td>{{ $data->packageName }}  </td>
                   <td>&#8358;{{ number_format($data->price, 2) }}</td>

                      <td><img src="{{asset($data->package_image)}}" width="30px;" height="30px;"> </td>
                    <td>{{ $data->cpts }}  </td>
                    <td>{{ $data->bottle }}  </td>
                      <td>
                      @if ($data->status === 'active')
                <span class="badge badge-rounded badge-success">Active</span>
                @elseif ($data->status === 'inactive')
                <span class="badge badge-rounded badge-danger">Inactive</span>
    
                @endif
                    </td>
                  <td>
              <div class="dropdown">
                <button type="button" class="btn btn-success light sharp" data-bs-toggle="dropdown">
                  <svg width="20px" height="20px" viewBox="0 0 24 24" version="1.1">
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                      <rect x="0" y="0" width="24" height="24" />
                      <circle fill="#000000" cx="5" cy="12" r="2" />
                      <circle fill="#000000" cx="12" cy="12" r="2" />
                      <circle fill="#000000" cx="19" cy="12" r="2" />
                    </g>
                  </svg>
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="/superadmin/package/edit_package/{{$data->id }}">Edit</a>
                  <a class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#basicModal{{$data->id }}"  >Delete</a>
                </div>
              </div>
            </td>

       
 
                  </tr>
<!--==== Delete Modal =======-->
            <div class="modal fade" id="basicModal{{$data->id }}">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Delete Package - {{$data ->packageName}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">Are you sure you want to delete {{$data ->packageName}} , you cant get it back again, once deleted</div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                     

                    <form method="POST" action="{{ route('superadmin.package.destroyPackage', $data->id) }}">
                        @csrf
                        <input name="_method" type="hidden" value="DELETE">
                        <button type="submit" class="btn btn-primary show_confirm" data-toggle="tooltip" title='Delete'>Delete Package</button>
                    </form>

                  </div>
                </div>
              </div>
                  
             
    <?php $no++; ?>
 @endforeach
</tbody></table>
          

 

</div>
</div></div></div></div><!-----end of row----->
    



  </div>
</div>

 

  

@include('layouts.footer_content')



@endsection
