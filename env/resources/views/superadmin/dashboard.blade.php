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
 <div class="row">
<div class=" col-lg-3 col-sm-6">
						<div class="widget-stat card bg-primary">
							<div class="card-body  p-4">
								<div class="media">
									<span class="me-3">
									<i class="fa-solid fa-users"></i>
									</span>
									<div class="media-body text-white">
										<p class="mb-1">Active Members</p>
										<h5 class="text-white">{{ $memberCount }}</h5>
										
										
									</div>
								</div>
							</div>
						</div>
                    </div>

<div class=" col-lg-3 col-sm-6">
						<div class="widget-stat card bg-warning">
							<div class="card-body  p-4">
								<div class="media">
									<span class="me-3">
							<i class="fa-solid fa-user-minus"></i>
									</span>
									<div class="media-body text-white">
										<p class="mb-1">Inactive Members</p>
										<h5 class="text-white">{{ $inactivememberCount }}</h5>
										
										
									</div>
								</div>
							</div>
						</div>
                    </div>


<div class=" col-lg-3 col-sm-6">
						<div class="widget-stat card bg-secondary">
							<div class="card-body  p-4">
								<div class="media">
									<span class="me-3">
							<i class="fa-solid fa-user-astronaut"></i>
									</span>
									<div class="media-body text-white">
										<p class="mb-1">Pending Member</p>
										<h5 class="text-white">{{ $pendingmemberCount }}</h5>
										
										
									</div>
								</div>
							</div>
						</div>
                    </div>


<div class=" col-lg-3 col-sm-6">
						<div class="widget-stat card bg-danger">
							<div class="card-body  p-4">
								<div class="media">
									<span class="me-3">
						<i class="fa-solid fa-store"></i>
									</span>
									<div class="media-body text-white">
										<p class="mb-1">Stockists</p>
										<h5 class="text-white">0</h5>
										
										
									</div>
								</div>
							</div>
						</div>
                    </div>


</div><!--========end row==========-->





<div class="row">
<div class="col-xl-3 col-xxl-3 col-lg-3 col-sm-6">
                        <div class="widget-stat card">
            
							<div class="card-body  p-2">
<span class="badge light text-white bg-red rounded-circle">
            {{ $pendingregCount }}
        </span>
								<div class="media ai-icon">
									<span class="me-3  text-danger">
									<i class="fa-regular fa-address-card"></i>
									</span>
									<div class="media-body">
										<p class="mb-3">pending Reg. payment  </p>
<a class="btn34" href="{{ route('superadmin.transaction.pendingRegistrationPayment') }}">Click Here</a>
									</div>
								</div>
             
							</div>
						</div>
                    </div>


                  

                       <div class="col-xl-3 col-xxl-3 col-lg-3 col-sm-6">
                        <div class="widget-stat card">
							<div class="card-body  p-2">
                <span class="badge light text-white bg-red rounded-circle">
            {{ $wallettopupCount }}
        </span>
								<div class="media ai-icon">
									<span class="me-3   text-success">
									<i class="fa-solid fa-credit-card"></i>
									</span>
									<div class="media-body">
										<p class="mb-3">pending Wallet Topup   </p>
<a class="btn34" href="{{ route('superadmin.transaction.pendingwallettopup') }}">Click Here</a>
									</div>
								</div>
							</div>
						</div>
                    </div>


               
 <div class="col-xl-3 col-xxl-3 col-lg-3 col-sm-6">
<div class="widget-stat card">
							<div class="card-body  p-2">
                <span class="badge light text-white bg-red rounded-circle">
            {{ $withdrawalCount }}
        </span>
								<div class="media ai-icon">
									<span class="me-3  text-danger">
									<i class="fa-solid fa-money-bills"></i>
									</span>
									<div class="media-body">
										<p class="mb-3">Pending Payout </p>
<a class="btn34" href="{{ route('superadmin.transaction.payout') }}">Click Here</a>
									</div>
								</div>
							</div>
						</div>
                    </div>













 <div class="col-xl-3 col-xxl-3 col-lg-3 col-sm-6">
<div class="widget-stat card">
							<div class="card-body  p-2">
								<!----
                <span class="badge light text-white bg-red rounded-circle">
            {{ $withdrawalCount }}
        </span>--->
								<div class="media ai-icon">
									<span class="me-3  text-danger">
									<i class="fa-solid fa-money-bills"></i>
									</span>
									<div class="media-body">
										<p class="mb-3">Payout History</p>
<a class="btn34" href="{{ route('superadmin.transaction.payout_history') }}">Click Here</a>
									</div>
								</div>
							</div>
						</div>
                    </div>


<div class="col-xl-3 col-xxl-3 col-lg-3 col-sm-6">
<div class="widget-stat card">
							<div class="card-body  p-2">
                <span class="badge light text-white bg-red rounded-circle">
            {{ $packageCount }}
        </span>
								<div class="media ai-icon">
									<span class="me-3  text-danger">
								<i class="fa-solid fa-wine-bottle"></i>
									</span>
									<div class="media-body">
										<p class="mb-3">Pending Package   </p>
<a class="btn34" href="{{ route('superadmin.package.pendingpackageOrder') }}">Click Here</a>
									</div>
								</div>
							</div>
						</div>
                    </div>



<div class="col-xl-3 col-xxl-3 col-lg-3 col-sm-6">
<div class="widget-stat card">
							<div class="card-body  p-2">
                <span class="badge light text-white bg-red rounded-circle">
            {{ $productCount }}
        </span>
								<div class="media ai-icon">
									<span class="me-3  text-danger">
								<i class="fa-solid fa-wine-bottle"></i>
									</span>
									<div class="media-body">
										<p class="mb-3">Pending Product   </p>
<a class="btn34" href="{{ route('superadmin.product.pendingproductOrder') }}">Click Here</a>
									</div>
								</div>
							</div>
						</div>
                    </div>



					<div class="col-xl-3 col-xxl-3 col-lg-3 col-sm-6">
<div class="widget-stat card">
							<div class="card-body  p-2">
            
								<div class="media ai-icon">
									<span class="me-3  text-danger">
							<i class="fa-solid fa-basket-shopping"></i>
									</span>
									<div class="media-body">
										<p class="mb-3">Package Order   </p>
<a class="btn34" href="{{ route('superadmin.package.approvepackageOrder') }}">Click Here</a>
									</div>
								</div>
							</div>
						</div>
                    </div>




					<div class="col-xl-3 col-xxl-3 col-lg-3 col-sm-6">
<div class="widget-stat card">
							<div class="card-body  p-2">
     
								<div class="media ai-icon">
									<span class="me-3  text-danger">
								<i class="fa-solid fa-bag-shopping"></i>
									</span>
									<div class="media-body">
										<p class="mb-3"> Product order   </p>
<a class="btn34" href="{{ route('superadmin.product.aproveproductOrder') }}">Click Here</a>
									</div>
								</div>
							</div>
						</div>
                    </div>



</div><!--========end col==========-->

<div class="row">




              <div class="col-xl-6">
								<div class="card" id="user-activity1">
									<div class="card-header border-0 pb-0">
										<h4 class="card-title mb-0">Monthly Total Bought Package ({{ $year }})</h4>
										<ul class="nav nav-tabs style-1 chart-tab" role="tablist">
										
									</div>
									<div class="card-body ps-sm-3 ps-0 pb-2">
										 
										<div>
							   <canvas id="approvedTransactionsChart" height="100"></canvas>
										</div>
									</div>
								</div>
							</div>




            <div class="col-xl-6">
								<div class="card" id="user-activity1">
									<div class="card-header border-0 pb-0">
										<h4 class="card-title mb-0">Monthly Total Bought Product ({{ $year }})</h4>
										<ul class="nav nav-tabs style-1 chart-tab" role="tablist">
										
									</div>
									<div class="card-body ps-sm-3 ps-0 pb-2">
										 
										<div>
							   <canvas id="approvedTransactionsproductChart" height="100"></canvas>
										</div>
									</div>
								</div>
							</div>


</div><!--========end row==========-->


  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const ctx1 = document.getElementById('approvedTransactionsChart').getContext('2d');
  new Chart(ctx1, {
    type: 'bar',
    data: {
      labels: [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ],
      datasets: [{
        label: 'Approved Transactions (₦)',
        data: @json($monthlyTotals),
        backgroundColor: '#28a745',
        borderColor: '#1b7e34',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return '₦' + value.toLocaleString();
            }
          },
          title: {
            display: true,
            text: 'Amount (₦)'
          }
        }
      }
    }
  });
</script>

<script>
  const ctx2 = document.getElementById('approvedTransactionsproductChart').getContext('2d');
  new Chart(ctx2, {
    type: 'bar',
    data: {
      labels: [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ],
      datasets: [{
        label: 'Approved Transactions (₦)',
        data: @json($monthlyproductTotals),
        backgroundColor: '#007bff',
        borderColor: '#0056b3',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return '₦' + value.toLocaleString();
            }
          },
          title: {
            display: true,
            text: 'Amount (₦)'
          }
        }
      }
    }
  });
</script>


@endsection
