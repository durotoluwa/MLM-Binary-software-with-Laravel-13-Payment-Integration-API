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
          <a href="">Buy Package For Member</a>
        </li>
      </ol>
    </div>
 

 

@if (auth()->user()->isImpersonated())
    <a href="{{ route('impersonate.leave') }}" class="btn btn-danger">
        Back to Admin
    </a>
@endif



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

 
<form action="{{ route('member.package.search') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group mb-4">
        <label style="color:black; font-weight:500; font-size:13px;">Enter  Member Username:</label>
        <input type="text" name="member" id="memberInput" required class="form-control" placeholder="Enter member username">
        <small style="font-size: 17px;" id="usernameFeedback" class="text-danger"></small>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Proceed</button>
</form>



<script>
document.getElementById('memberInput').addEventListener('keyup', function () {
    let username = this.value;

    if (username.length > 2) { // only check after 3+ chars
        fetch("{{ route('member.package.checkUsername') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ member: username })
        })
        .then(response => response.json())
        .then(data => {
            let feedback = document.getElementById('usernameFeedback');
            feedback.textContent = data.message;
            feedback.className = data.exists ? "text-success" : "text-danger";
        });
    }
});
</script>






    </div><!--========end row==========-->

 
  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
