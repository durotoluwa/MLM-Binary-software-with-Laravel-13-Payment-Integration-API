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
    <div class="row">
@if(auth()->user()->is_muted == 1)
    <div class="alert alert-warning">
        <strong>Notice:</strong> Your account is currently <strong>muted</strong> Some features may be restricted. Please contact support for assistance.
    </div>
@endif

 
@if(session('warning'))
<div class="alert alert-warning">
    {{ session('warning') }}
</div>
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
 

@if (auth()->user()->isImpersonated())
    <a href="{{ route('impersonate.leave') }}" class="btn btn-danger">
        Back to Admin
    </a>
@endif


 <div class="row">
  <div class="col-xl-12">

 <div class="card profile-overview profile-overview-wide">
<div class="card-body d-flex">

  <div class="clearfix">
        <div class="d-inline-block position-relative me-sm-4 me-3 mb-3 mb-lg-0">
           

<img src="{{ !empty(Auth::user()->profile_photo_path) ? Auth::user()->profile_photo_path : asset('default/avatar.png') }}" width="100" class="rounded-4 profile-avata">

            <span class="fa fa-circle border border-3 border-white text-success position-absolute bottom-0 end-0 rounded-circle"></span>
        </div>
    </div>

    <div class="clearfix d-xl-flex flex-grow-1">
        <div class="clearfix pe-md-5">
            <h3 class="fw-semibold mb-1">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }} <img src="{{ asset('images/blue-tick.png') }}" alt="Blue Tick"></h3>
            <ul class="d-flex flex-wrap fs-6 align-items-center">
                <li class="me-3 d-inline-flex align-items-center"><i class="fa-solid fa-user" style="margin-right: 5px;"></i> {{ Auth::user()->username }}</li>
                <li class="me-3 d-inline-flex align-items-center"><i class="fa-solid fa-location-dot" style="margin-right: 5px;"></i> 420 City Path, AU 123-456</li>
                <li class="me-3 d-inline-flex align-items-center"><i class="fa-solid fa-envelope" style="margin-right: 5px;"></i> {{ Auth::user()->email }} </li>
            </ul>
            <div class="d-md-flex d-none flex-wrap">
                <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                    <div class="avatar avatar-md bg-primary-light text-primary rounded d-flex align-items-center justify-content-center">
                      ₦
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">{{ number_format(Auth::user()->withdraw_wallet_balance, 2) }}
</h3>
                        <span class="fs-14"> Withdrawal Wallet</span>
                    </div>
                </div>
     <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                    <div class="avatar avatar-md bg-primary-light text-primary rounded d-flex align-items-center justify-content-center">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="clearfix ms-2">
                        <h3 class="mb-0 fw-semibold lh-1">{{ Auth::user()->transaction_pin }}</h3>
                        <span class="fs-14">Transaction Pin</span>
                    </div>
                </div> 


  @php
    $pendingTotal = Auth::user()
        ->payout()
        ->whereIn('status', ['pending', 'processing'])
        ->sum('amount');
@endphp

@if($pendingTotal > 0)
    <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
        <div class="avatar avatar-md bg-primary-light text-primary rounded d-flex align-items-center justify-content-center">
            ₦
        </div>
        <div class="clearfix ms-2">
            <h3 class="mb-0 fw-semibold lh-1">{{ number_format($pendingTotal, 2) }}</h3>
            <span class="fs-14"> Pending Withdrawal </span>
        </div>
    </div>
@endif



              
           


             


            </div>
        </div>


              <div class="clearfix mt-3 mt-xl-0 ms-auto d-flex flex-column col-xl-4">
            <div class="clearfix mb-3 text-xl-end">
              <h5 class="fw-semibold mb-1"> Matching Bonus Points </h5>
                        <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                    <div class="clearfix ms-2">
                        <h4 class="mb-0 fw-semibold lh-1">{{ Auth::user()->left_ctp_for_matching }} cpts </h4>
                        <span class="fs-14">Left Leg</span>
                    </div>
                </div> 


                     <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                    <div class="clearfix ms-2">
                        <h4 class="mb-0 fw-semibold lh-1">{{ Auth::user()->right_ctp_for_matching }} cpts </h4>
                        <span class="fs-14">Right Leg</span>
                    </div>
                </div> 
       
            </div>
           
        </div>
    </div>
</div>
   
</div></div> 
 </div>
  </div>
<!--================== end of profile-overview  =================-->





 <div class="row">
  <div class="col-xl-12">
<div class="card">
<div class="card-body">
<div class="row separate-row">


                  <div class="col-sm-3">
                    <div class="job-icon pb-4 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h4 class="mb-0 lh-1"> {{ Auth::user()->user_rank}}   </h4>
                         
                        </div>
                         <span class="d-block mb-2">Current Rank</span>
                      </div>
                          <i class="fa-solid fa-arrows-up-to-line" style="color:red;  font-size:30px;" ></i>
                    </div>
                  </div>


                  <div class="col-sm-3">
              <div class="job-icon pb-4 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h4 class="mb-0 lh-1"> {{ Auth::user()->user_plan }} </h4>
                        </div>
                        <span class="d-block mb-2">Current Plan</span>
                      </div>
                   <i class="fa-solid fa-arrows-to-eye" style="color:#6FB310;  font-size:30px;"></i>
                    </div>
                  </div>


                  <div class="col-sm-3">
                    <div class="job-icon pb-4 d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h4 class="mb-0 lh-1">₦{{ number_format($user->unilevel_wallet_balance, 2) }} </h4>
                         
                        </div>
                        <span class="d-block mb-2">Unilevel Wallet</span>
                      </div>
                             <i class="fa-solid fa-credit-card" style="color:#FF9900;  font-size:30px;"></i>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="job-icon pt-4  d-flex justify-content-between">
                      <div>
                        <div class="d-flex align-items-center mb-1">
                          <h4 class="mb-0 lh-1">₦{{ number_format($user->deposit_wallet_balance, 2) }}</h4>
                        </div>
                        <span class="d-block mb-2">Deposite Wallet Balance</span>
                      </div>
                     
                      
                    </div>
                     <a href="{{ route('user.topup_wallet') }}" class="topupbtn"  >Top-up Wallet</a>
                  </div></div>
                  
</div></div>
 </div><!--========end col==========-->
    </div><!--========end row==========-->


 <div class="row">
<div class="col-xl-6 col-xxl-3 col-lg-3 col-sm-6">
						<div class="widget-stat card bg-primary">
							<div class="card-body  p-4">
								<div class="media">
									<span class="me-3">
									  <i class="fa-regular fa-star"></i>
									</span>
									<div class="media-body text-white">
										<p class="mb-1">Total Points</p>
										<h5 class="text-white">{{$user->total_ctp}} CPT</h5>
										
										
									</div>
								</div>
							</div>
						</div>
                    </div>

<div class="col-xl-6 col-xxl-3 col-lg-3 col-sm-6">
						<div class="widget-stat card bg-warning">
							<div class="card-body  p-4">
								<div class="media">
									<span class="me-3">
								<i class="fa-solid fa-up-down"></i>
									</span>
									<div class="media-body text-white">
										<p class="mb-1">Down-lines</p>
										<h5 class="text-white">{{ $downlineCount }}</h5>
										
										
									</div>
								</div>
							</div>
						</div>
                    </div>




                    <div class="col-xl-6 col-xxl-3 col-lg-3 col-sm-6">
						<div class="widget-stat card bg-secondary">
							<div class="card-body  p-4">
								<div class="media">
									<span class="me-3">
								<i class="fa-solid fa-user-tie"></i>
									</span>
									<div class="media-body text-white">
										<p class="mb-1">Sponsors</p>
										<h5 class="text-white">{{ $sponsoredCount }}</h5>
										
										
									</div>
								</div>
							</div>
						</div>
                    </div>





                    <div class="col-xl-6 col-xxl-3 col-lg-3 col-sm-6">
						<div class="widget-stat card bg-danger">
							<div class="card-body  p-4">
								<div class="media">
									<span class="me-3">
								<i class="fa-solid fa-coins"></i>
									</span>
									<div class="media-body text-white">
										<p class="mb-1">Withdrawals</p>
										<h5 class="text-white">{{ $withdrawaCount }}</h5>
										
										
									</div>
								</div>
							</div>
						</div>
                    </div>


</div><!--========end row==========-->


<div class="row">

   <div class="col-xl-3 col-xxl-4 col-lg-4 col-sm-6">
                        <div class="widget-stat card">
							<div class="card-body  p-4">
								<div class="media ai-icon">
									<span class="me-3 bgl-danger text-danger">
									<i class="fa-solid fa-wine-bottle"></i>
									</span>
									<div class="media-body">
										<p class="mb-1">Pacakage </p>
<a class="btn btn-square btn-outline-primary" href="{{ route('user.package') }}">Buy Package</a>
									</div>
								</div>
							</div>
						</div>
                    </div>


                  

                       <div class="col-xl-3 col-xxl-4 col-lg-4 col-sm-6">
                        <div class="widget-stat card">
							<div class="card-body  p-4">
								<div class="media ai-icon">
									<span class="me-3  bgl-success text-success">
									<i class="fa-solid fa-bottle-water"></i>
									</span>
									<div class="media-body">
										<p class="mb-1">Product </p>
<a class="btn btn-square btn-outline-primary" href="{{ route('user.order_product') }}">Buy Product</a>
									</div>
								</div>
							</div>
						</div>
                    </div>


               


<div class="col-xl-3 col-xxl-4 col-lg-4 col-sm-12">
<div class="widget-stat card">
							<div class="card-body  p-4">
								<div class="media ai-icon">
									<span class="me-3 bgl-danger text-danger">
									<i class="fa-solid fa-money-bills"></i>
									</span>
									<div class="media-body">
										<p class="mb-1">Withdraw </p>
<a class="btn btn-square btn-outline-primary" href="{{ route('user.withdrawal_page') }}">Request For Withdrawal</a>
									</div>
								</div>
							</div>
						</div>
                    </div>

</div><!--========end col==========-->




 <div class="row">
<div class="col-xl-12">
 <div class="card">
                            <div class="card-header">
                      
                            </div>
                            <div class="card-body">
                                <!-- Nav tabs -->
                                <div class="default-tab">
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#home"><i class="la la-home me-2"></i> Membership</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#profile"><i class="la la-user me-2"></i> Bonus History</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#contact"><i class="la la-phone me-2"></i> Package History</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#message"><i class="la la-envelope me-2"></i> Re-order History</a>
                                        </li>

                                           <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#Deposits"><i class="la la-envelope me-2"></i>Deposits</a>
                                        </li>
  <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#Withdrawal"><i class="la la-envelope me-2"></i>Withdrawal</a>
                                        </li>

                                          <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#downline"><i class="la la-envelope me-2"></i>Downlines</a>
                                        </li>

                                         <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#Genealogy"><i class="la la-envelope me-2"></i>Genealogy</a>
                                        </li>
                                        
                                        
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="home" role="tabpanel">
                                            <div class="pt-4">

                                                

                                <div class="row">
                    {{-- Upline --}}
<div class="col-xl-12 col-xxl-12 col-lg-12 col-sm-12">
    <div class="widget-stat card">
        <div class="card-body p-4">
            <div class="media ai-icon">
                <span class="me-3 bgl-danger text-danger">
                    <img src="{{ !empty(Auth::user()->uplineuser?->profile_photo_path) ? Auth::user()->uplineuser->profile_photo_path : asset('default/avatar.png') }}" 
                         width="50" class="rounded-4 profile-avata">
                </span>
                <div class="media-body">
                    <p class="mb-1">UPLINE:</p>
                    <h4 class="mb-0 fw-semibold lh-1">
                        {{ Auth::user()->uplineuser?->first_name }} {{ Auth::user()->uplineuser?->last_name }}
                    </h4>
                    <span class="d-block mb-2">{{ Auth::user()->uplineuser?->username }}</span>
                    <span class="badge bg-success">{{ Auth::user()->uplineuser?->user_plan }} Plan</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sponsor --}}
<div class="col-xl-12 col-xxl-12 col-lg-12 col-sm-12">
    <div class="widget-stat card">
        <div class="card-body p-4">
            <div class="media ai-icon">
                <span class="me-3 bgl-danger text-danger">
                    <img src="{{ !empty(Auth::user()->sponsoruser?->profile_photo_path) ? Auth::user()->sponsoruser->profile_photo_path : asset('default/avatar.png') }}" 
                         width="50" class="rounded-4 profile-avata">
                </span>
                <div class="media-body">
                    <p class="mb-1">SPONSOR:</p>
                    <h4 class="mb-0 fw-semibold lh-1">
                        {{ Auth::user()->sponsoruser?->first_name }} {{ Auth::user()->sponsoruser?->last_name }}
                    </h4>
                    <span class="d-block mb-2">{{ Auth::user()->sponsoruser?->username }}</span>
                    <span class="badge bg-success">{{ Auth::user()->sponsoruser?->user_plan }} Plan</span>
                </div>
            </div>
        </div>
    </div>
</div>

</div><!-- end row -->

</div>
</div>
<div class="tab-pane fade" id="profile">
<div class="pt-4">
<h4>List of all Bonuses</h4>
   	<table id="responsiveTable" class="display responsive nowrap w-100">
									<thead>
										<tr>
                      <th>#</th>
											<th>Name</th>
											<th>Type</th>
                      <th>Amount</th>
											<th>Description</th>
											<th>Status</th>
											<th>Date</th>
										
										</tr>
									</thead>
									<tbody>

					@foreach($bonuses as $index => $bonus)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $bonus->user->first_name }}</td>
                <td>{{ ucfirst($bonus->type) }}</td>
                <td>₦{{ number_format($bonus->amount, 2) }}</td>
                <td>{{ $bonus->description }}</td>
              <td>
    @if($bonus->is_paid)
        <span class="badge bg-success">Paid</span>
    @else
        <span class="badge bg-warning text-dark">Unpaid</span>
    @endif
</td>

                <td>{{ $bonus->created_at->format('d M, Y h:i A') }}</td>
            </tr>
            @endforeach

									
									</tbody>
								</table>
    
</div>
</div>
                                        <div class="tab-pane fade" id="contact">
                                            <div class="pt-4">
                                        <h4>Package History</h4>
                                                
<div class="d-flex recent-activity">
  
      @foreach($userpack as $index => $package)
                      	<div class="d-flex align-items-center list-item-bx">
												<div class="icon-img-bx">
													<svg xmlns="http://www.w3.org/2000/svg" width="71" height="71" viewBox="0 0 71 71">
													<g  transform="translate(-457 -443)">
														<rect  width="71" height="71" rx="12" transform="translate(457 443)" fill="#c5c5c5"/>
														<g  transform="translate(457 443)">
														<rect  data-name="placeholder" width="71" height="71" rx="12" fill="#2769ee"/>
														<circle  data-name="Ellipse 12" cx="18" cy="18" r="18" transform="translate(15 20)" fill="#fff"/>
														<circle  data-name="Ellipse 11" cx="11" cy="11" r="11" transform="translate(36 15)" fill="#ffe70c" style="mix-blend-mode: multiply;isolation: isolate"/>
														</g>
													</g>
													</svg>
												</div>
												<div class="ms-3">
													<h6 class="mb-1">{{ $package->package->packageName }} Package</h6>
													<p class="mb-0">₦{{ number_format($package->amount_paid, 2) }}</p>
<p class="mb-0">{{ \Carbon\Carbon::parse($package->approved_at)->format('F j, Y') }}</p>

 
												</div>
											</div>
										

 @endforeach
                  

</div>


                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="message">
                                            <div class="pt-4">
                                              <h4>Re-order History</h4>
      <table class="table table-bordered table-responsive-sm">
    <thead>
        <tr>
            <th>#</th>
            <th>Order No</th>
            <th>Amount</th>
            <th>Payment Method</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $key => $order)
            <tr>
                <th>{{ $key + 1 }}</th>
                <td>{{ $order->order_no }}</td>
                <td>{{ number_format($order->amount, 2) }}</td>
                <td>{{ ucfirst($order->payment_method) }}</td>
                <td>
                    @if($order->status === 'approved')
                        <span class="badge bg-success">Approved</span>
                    @elseif($order->status === 'pending')
                        <span class="badge bg-warning">Pending</span>
                    @else
                        <span class="badge bg-danger">{{ ucfirst($order->status) }}</span>
                    @endif
                </td>
                <td>{{ $order->created_at->format('F j, Y g:i A') }}</td>
                <td>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#orderModal{{ $order->id }}">
                        View Order
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No orders found</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Put all modals OUTSIDE the table --}}
@foreach($orders as $order)
<div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1"
     aria-labelledby="orderModalLabel{{ $order->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel{{ $order->id }}">
                    Order #{{ $order->order_no }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
  

                <h6>Order Items</h6>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>APC</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                 <td>{{ number_format($item->apc, 2) }}</td>
                                <td>{{ number_format($item->apc + $item->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <h2> Total Amount: ₦{{ number_format($order->amount, 2) }}</h2>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach
</div>
</div>



                         <div class="tab-pane fade" id="Deposits">
                                            <div class="pt-4">
                                              <h4>Deposits History</h4>
      <table id="example" class="display" >									
  <thead>
										<tr>
                      <th>#</th>
											<th>Name</th>
											<th>Payment Method</th>
                      <th>Amount</th>
										 
											<th>Status</th>
											<th>Date</th>
										
										</tr>
									</thead>
									<tbody>

					@foreach($walletropup as $index => $wallet)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $wallet->user->first_name }} {{ $wallet->user->last_name }}</td>
                <td>{{ $wallet->payment_method }}</td>
                <td>₦{{ number_format($wallet->amount, 2) }}</td>
                
              <td>
    @if($wallet->status == 'approved')
        <span class="badge bg-success">Approved</span>
    @else
        <span class="badge bg-warning text-dark">Pending</span>
    @endif
</td>

                <td>{{ $wallet->created_at->format('d M, Y h:i A') }}</td>
            </tr>
            @endforeach

									
									</tbody>
								</table>
</div>
</div>



                 <div class="tab-pane fade" id="Withdrawal">
                                            <div class="pt-4">
                                              <h4>Withdrawal History</h4>
       <table id="example4" class="display" style="min-width: 845px">
                                        <thead>
                                            <tr>
                                              <th>#</th>
                                                <th>Full Name</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
											
                                            </tr>
                                        </thead>
                                        <tbody>
                                    	@foreach($withdrawals as $index => $data)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $data->user->first_name }} {{ $data->user->last_name }}</td>
                 <td>₦{{ number_format($data->amount, 2) }}</td>
                
              <td>
    @if($data->status == 'approved')
        <span class="badge bg-success">Approved</span>
    @else
        <span class="badge bg-warning text-dark">Pending</span>
    @endif
</td>

                <td>{{ $data->created_at->format('d M, Y h:i A') }}</td>
            </tr>
            @endforeach
                       
                                        </tbody>
                                    </table>
</div>
</div>


<div class="tab-pane fade" id="downline">
    <div class="pt-4">
        <h4>My Downlines</h4>
        <table id="example4" class="display" style="min-width: 845px">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Stage</th>
                    <th>Plan</th>
                    <th>Up-Line</th>
                    <th>Sponsor</th>
                    <th>Position</th>
                </tr>
            </thead>
            <tbody>
                @forelse($downlines as $index => $downline)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $downline->first_name }} {{ $downline->last_name }}</td>
                        <td>{{ $downline->user_rank ?? 'Starter' }}</td>
                        <td>{{ $downline->user_plan ?? '-' }}</td>
                        <td>{{ $downline->upline?->username ?? '-' }}</td>
                        <td>{{ $downline->sponsor?->username ?? '-' }}</td>
                      <td>
    @if($downline->position === 'left')
        <span class="badge" style="background-color: green; color: white;">
            {{ ucfirst($downline->position) }}
        </span>
    @elseif($downline->position === 'right')
        <span class="badge" style="background-color: blue; color: white;">
            {{ ucfirst($downline->position) }}
        </span>
    @else
        <span class="badge bg-secondary"></span>
    @endif
</td>

                    </tr>
                @empty
                
                
                @endforelse
            </tbody>
        </table>
    </div>
</div>



<div class="tab-pane fade" id="Genealogy">
    <div class="pt-4">
        <h4>My Genealogy</h4>
    <div style="overflow-x:auto; width:100%;">
    <table id="genealogyTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Stage</th>
                <th>Plan</th>
                <th>Up-Line</th>
                <th>Sponsor</th>
                <th>Position</th>
                <th>C-CPTs</th>
                <th>Reg. Date</th>
                <th>P-C-CPts</th>
                <th>Current C-CPts</th>
                <th>Current P-C-CPts</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($genelogy as $index => $downline)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $downline->first_name }} {{ $downline->last_name }} <br> ({{ $downline->username }})</td>
                <td>{{ $downline->user_rank ?? '-' }}</td>
                <td>{{ $downline->user_plan ?? '-' }}</td>
                <td>
                    {{ optional($downline->upline)->first_name ?? '-' }} {{ optional($downline->upline)->last_name ?? '' }}
                    <br> ({{ optional($downline->upline)->username ?? '' }})
                </td>
                <td>
                    {{ optional($downline->sponsor)->first_name ?? '-' }} {{ optional($downline->sponsor)->last_name ?? '' }}
                    <br> ({{ optional($downline->sponsor)->username ?? '' }})
                </td>
                <td>
                    <span class="badge 
                        @if($downline->position === 'left') bg-success 
                        @elseif($downline->position === 'right') bg-primary 
                        @else bg-secondary @endif">
                        {{ ucfirst($downline->position) ?? '-' }}
                    </span>
                </td>
                <td>{{ $downline->total_ctp ?? 0 }}</td>
                <td>{{ $downline->created_at->format('d M, Y') }}</td>
                <td>{{ $downline->p_c_cpts ?? 0 }}</td>
                <td>{{ $downline->current_c_cpts ?? 0 }}</td>
                <td>{{ $downline->current_p_c_cpts ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

    </div>
</div>



                                    </div>
                                </div>
                            </div>
                        </div>
</div></div><!--========end col==========-->
</div><!--========end row==========-->






 <div class="row ">
<div class="card-body  p-4">
<div class="row d-flex justify-content-between">
<div class="col-xl-12 col-xxl-12 col-lg-12 col-sm-12">
<div class="widget-stat card p-4">
<style>
.node-style {
    border: 2px solid #4CAF50;
    border-radius: 10px;
    padding: 10px;
    background-color: #fff;
    box-shadow: 0px 2px 6px rgba(0,0,0,0.2);
    text-align: center;
    width: 130px;
}

.node-style img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-bottom: 8px;
}

.node-style .node-name {
    font-size: 12px;
    font-weight: bold;
    color: #333;
}

.node-style .node-username {
    font-size: 10px;
    color: #666;
}

/* Sponsor special style */
.sponsor-style {
    border: 2px solid #FF9800;
    background-color: #FFF3E0;
}
</style>
@php
// Prevent redeclaring function if Blade partial loads multiple times
if (!function_exists('renderNode')) {
    function renderNode($node) {
        if (empty($node) || !is_array($node)) {
            return null;
        }

        // Safe defaults
        $name     = $node['name'] ?? 'Unknown';
        $username = $node['username'] ?? '';
        $photo    = $node['photo'] ?? 'default.png';

        // ✅ Leg label always comes from propagated leg
        $legLabel = !empty($node['leg']) ? ' (' . $node['leg'] . ')' : '';

        $class    = 'node-style'; // Always default style
        $imageUrl = asset('images/' . $photo);

        // Build node data
        $nodeData = [
            'text' => [
                'name'  => $name,
                'title' => $username . $legLabel,
            ],
            'image'     => $imageUrl,
            'HTMLclass' => $class,
        ];

        // Recursively render children
        if (!empty($node['children']) && is_array($node['children'])) {
            $children = [];
            foreach ($node['children'] as $child) {
                $childNode = renderNode($child);
                if ($childNode) $children[] = $childNode;
            }

            if (!empty($children)) {
                $nodeData['children'] = $children;
            }
        }

        return $nodeData;
    }
}

// Safely render the root node from $tree provided by controller
$nodeStructure = renderNode($tree ?? null);

// If $tree is null or malformed, create a fallback so Treant won’t break
if ($nodeStructure === null) {
    $nodeStructure = [
        'text'      => ['name' => 'No Members Found', 'title' => ''],
        'HTMLclass' => 'node-style',
    ];
}

// Build the JSON configuration for Treant
$treeJson = json_encode([
    'chart' => [
        'container'          => "#tree-container",
        'levelSeparation'    => 60,
        'siblingSeparation'  => 40,
        'subTeeSeparation'   => 40,
        'node'               => ['HTMLclass' => "node-style"],
        'connectors'         => ['type' => 'step'],
        'animation'          => [
            'nodeAnimation'       => "easeOutBounce",
            'nodeSpeed'           => 700,
            'connectorsAnimation' => "bounce",
            'connectorsSpeed'     => 700,
        ],
    ],
    'nodeStructure' => $nodeStructure,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp


<div id="tree-container" style="width: 100%; height: auto;"></div>

@if (!empty($tree['has_more']) && $tree['has_more'] === true)
    <div style="text-align:center; margin-top:20px;">
        <button id="load-more-btn"
                style="padding:10px 20px; font-size:14px; background-color:#4CAF50; color:white; border:none; border-radius:5px; cursor:pointer;">
            Load More Members
        </button>
    </div>
@endif

<script>
    let currentOffset = {{ $offset ?? 0 }};
    const userId = "{{ $tree['id'] ?? 0 }}";

    // Initialize Treant with server-rendered JSON
    (function initTree() {
        try {
            new Treant({!! $treeJson !!});
        } catch (error) {
            console.error("Treant rendering failed:", error);
            document.getElementById('tree-container').innerHTML =
                '<div style="color:red;text-align:center;">Unable to render genealogy tree.</div>';
        }
    })();

    // External Load More button
    document.getElementById('load-more-btn')?.addEventListener('click', async function() {
        currentOffset += 3; // Adjust based on your pagination limit

        try {
            const res = await fetch(`/tree/paginate/${userId}?offset=${currentOffset}`);
            if (!res.ok) throw new Error('Failed to load more members');
            const data = await res.json();

            // Render new subtree
            function renderClientNode(node) {
                if (!node || typeof node !== 'object') return null;

                const name     = node.name || 'Unknown';
                const username = node.username || '';
                const photo    = node.photo || 'default.png';
                const legLabel = node.leg ? ` (${node.leg})` : '';
                const className = 'node-style'; // Always default style
                const imageUrl = "{{ asset('images') }}/" + photo;

                const out = {
                    text: { name: name, title: username + legLabel },
                    image: imageUrl,
                    HTMLclass: className,
                    children: []
                };

                if (Array.isArray(node.children)) {
                    node.children.forEach(child => {
                        const renderedChild = renderClientNode(child);
                        if (renderedChild) out.children.push(renderedChild);
                    });
                }

                return out;
            }

            const renderedSubtree = renderClientNode(data);

            const newTreeConfig = {
                chart: {
                    container: "#tree-container",
                    levelSeparation: 60,
                    siblingSeparation: 40,
                    subTeeSeparation: 40,
                    node: { HTMLclass: "node-style" },
                    connectors: { type: 'step' },
                    animation: {
                        nodeAnimation: "easeOutBounce",
                        nodeSpeed: 700,
                        connectorsAnimation: "bounce",
                        connectorsSpeed: 700,
                    }
                },
                nodeStructure: renderedSubtree
            };

            new Treant(newTreeConfig);

            if (!data.has_more) {
                document.getElementById('load-more-btn')?.remove();
            }
        } catch (err) {
            console.error('Pagination load failed:', err);
            alert('Unable to load more members.');
        }
    });
</script>


 




<!--==========
<div class="col-xl-12 col-xxl-4 col-lg-4 col-sm-12">
sdfsdf
</div>========-->


</div><!--========end row==========-->
</div>

</div><!--==========end col==========-->
 

@include('layouts.footer_content')

 


@endsection
