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
            <a href="">Change Sponsor / Upline for {{ $user->username }}</a>
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
<div class="container py-5">
 

  <form action="{{ route('superadmin.member.updateSponsor', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label for="sponsor_id" class="form-label">New Sponsor Username</label>
        <input type="text" id="sponsor_id" name="sponsor_id" class="form-control"
               value="{{ old('sponsor_id', optional($user->sponsor)->username ?? '') }}" required>
        <div id="sponsor_info" class="form-text text-muted"></div>
    </div>

    <div class="mb-3">
        <label for="parent_id" class="form-label">New Upline Username</label>
        <input type="text" id="parent_id" name="parent_id" class="form-control"
               value="{{ old('parent_id', optional($user->upline)->username ?? '') }}" required>
        <div id="upline_info" class="form-text text-muted"></div>
    </div>

    <div class="mb-3">
        <label for="position" class="form-label">Position</label>
        <select name="position" class="form-control">
            <option value="left" {{ $user->position === 'left' ? 'selected' : '' }}>Left</option>
            <option value="right" {{ $user->position === 'right' ? 'selected' : '' }}>Right</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Update Sponsor / Upline</button>
</form>





   
 
</div>

<!-- Simple styling -->
<style>
    .form-step { display: none; }
    .form-step.active { display: block; }
</style>

 

</div></div></div></div><!-----end of row----->
    



  </div>
</div>

  

  

@include('layouts.footer_content')



{{-- AJAX Username Validation --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    function checkUsername(inputId, infoId) {
        const username = document.getElementById(inputId).value;
        const infoBox = document.getElementById(infoId);

        if (username.length < 3) {
            infoBox.innerHTML = '';
            return;
        }

        const baseUrl = "{{ url('/superadmin/member/validate-username') }}";

        fetch(`${baseUrl}?username=${encodeURIComponent(username)}`)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    infoBox.innerHTML = `<span class="text-success">✅ ${data.first_name} ${data.last_name} found</span>`;
                } else {
                    infoBox.innerHTML = `<span class="text-danger">❌ User not found</span>`;
                }
            })
            .catch((error) => {
                console.error('Validation error:', error);
                infoBox.innerHTML = `<span class="text-danger">Error checking username</span>`;
            });
    }

    document.getElementById('sponsor_id').addEventListener('keyup', () => checkUsername('sponsor_id', 'sponsor_info'));
    document.getElementById('parent_id').addEventListener('keyup', () => checkUsername('parent_id', 'upline_info'));
});
</script>


@endsection
