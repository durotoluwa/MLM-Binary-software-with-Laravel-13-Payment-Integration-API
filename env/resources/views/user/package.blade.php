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
          <a href="">Package</a>
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


@if($all->isEmpty())
    <div class="col-12">
        <div class="alert alert-info text-center">
            No packages available.
        </div>
    </div>
@else
    @foreach ($all as $data)
        @php
            $pkgIdx = array_search(strtolower(trim($data->packageName)), $rank);
            $locked = $pkgIdx !== false && $pkgIdx <= $currentIndex; 
        @endphp

        <div class="col-lg-12 col-xl-6 col-xxl-4">
            <div class="card {{ $locked ? 'bg-light text-muted' : '' }}">
                <div class="card-body">
                    <div class="row m-b-30">
                        <div class="col-md-5 col-xxl-12">
                            <div class="new-arrival-product mb-4 mb-xxl-4 mb-md-0">
                                <div class="new-arrivals-img-contnent">
                                    <img src="{{ asset($data->package_image) }}" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 col-xxl-12">
                            <div class="new-arrival-content position-relative">
                                <h4>{{ $data->packageName }}</h4>

                                @if(!$locked)
                                   <p>Bottle: <span class="item">{{ $data->bottle }}</span></p>
                                    <p>CPT: <span class="item">{{ $data->cpts }}</span></p>
@php
    // Base upgrade price = current package price minus last package price (never below 0)
    $upgradePrice = max(($data->price ?? 0) - ($lastPackagePrice ?? 0), 0);
    $apcTotal = ($data->apc ?? 0) * ($data->bottle ?? 0);
@endphp

<p>Price: <span class="item">₦{{ number_format($upgradePrice, 2) }}</span></p>

<p>
    APC:
    <span class="item">
        ₦{{ number_format($data->apc, 2) }}
        × {{ $data->bottle }} Bottles =
        ₦{{ number_format($apcTotal, 2) }}
    </span>
</p>

<p style="color: red; font-size:16px; font-weight:bold;">
    ₦{{ number_format($upgradePrice + $apcTotal, 2) }}
</p>



                                    <a href="{{ route('user.purchase-package', $data->id) }}" class="btn btn-primary">
                                        Buy Package <span class="btn-icon-end"><i class="fa fa-shopping-cart"></i></span>
                                    </a>
                                @else
                                   
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif



    </div><!--========end row==========-->

 
  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
