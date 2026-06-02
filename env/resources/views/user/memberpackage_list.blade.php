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
          <a href="">Buy Package for - {{ $user->first_name }} {{ $user->last_name }} - ({{ $user->username }})</a>
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
            $pkgIdx   = array_search(strtolower(trim($data->packageName)), $rank);
            $locked   = $pkgIdx !== false && $pkgIdx <= $currentIndex; 
            $lastUserPkg = \App\Models\userpackage::where('user_id', $user->id)
                ->where('status', 'approved')
                ->latest('id')
                ->first();

            // Bottle counts
            $bottleCount = $data->bottle;
            if ($lastUserPkg) {
                $prevBottle  = $lastUserPkg->previous_bottle ?? 0;
                $bottleCount = max($data->bottle - $prevBottle, 0);
            }

            // CPT values
            $packageCpt = $data->cpts;
            $currentCpt = $packageCpt;
            if ($lastUserPkg) {
                $prevCpt   = $lastUserPkg->previous_package_cpt ?? 0;
                $currentCpt = max($packageCpt - $prevCpt, 0);
            }

            // Prices
            $lastPackagePrice = 0;
            if ($lastUserPkg) {
                $prevPkg = \App\Models\Package::find($lastUserPkg->package_id);
                $lastPackagePrice = $prevPkg ? ($prevPkg->price ?? 0) : 0;
            }
            $upgradePrice = max(($data->price ?? 0) - $lastPackagePrice, 0);

            // APC totals
            $apcTotal        = ($data->apc ?? 0) * ($data->bottle ?? 0);
            $currentApcTotal = 0;
            if ($lastUserPkg) {
                $prevBottle    = $lastUserPkg->previous_bottle ?? 0;
                $currentBottle = max($data->bottle - $prevBottle, 0);
                $currentApcTotal = ($data->apc ?? 0) * $currentBottle;
            }

            $isUpgrade = $lastUserPkg ? true : false;
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
                                    <p>Bottle: <span class="item">{{ $bottleCount }}</span></p>
                                    <p>Package CPT: <span class="item">{{ $packageCpt }}</span></p>

                                    @if($isUpgrade)
                                        <p>Current CPT: <span class="item">{{ $currentCpt }}</span></p>
                                    @endif

                                    <p>Price: <span class="item">₦{{ number_format($upgradePrice, 2) }}</span></p>

                                    <p>
                                        Main APC:
                                        <span class="item">
                                            ₦{{ number_format($data->apc, 2) }}
                                            × {{ $data->bottle }} Bottles =
                                            ₦{{ number_format($apcTotal, 2) }}
                                        </span>
                                    </p>

                                    @if($isUpgrade)
                                        <p style="color: green; font-weight: bold;">
                                            Current APC:
                                            <span class="item" style="color: green; font-weight: bold;">
                                                ₦{{ number_format($data->apc, 2) }}
                                                × {{ $bottleCount }} Bottles =
                                                ₦{{ number_format($currentApcTotal, 2) }}
                                            </span>
                                        </p>
                                    @endif

                                    {{-- Final total depending on upgrade mode --}}
                                    @if($isUpgrade)
                                        <p style="color: red; font-size:16px; font-weight:bold;">
                                            ₦{{ number_format($upgradePrice + $currentApcTotal, 2) }}
                                        </p>
                                    @else
                                        <p style="color: red; font-size:16px; font-weight:bold;">
                                            ₦{{ number_format($upgradePrice + $apcTotal, 2) }}
                                        </p>
                                    @endif

                                    <a href="{{ route('user.member-purchase-package', [$user->id, $data->id]) }}" class="btn btn-primary">
                                        Buy Package <span class="btn-icon-end"><i class="fa fa-shopping-cart"></i></span>
                                    </a>
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
