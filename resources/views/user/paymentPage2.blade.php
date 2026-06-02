@extends('layouts.app')
@section('content')


@include('layouts.navbar')
 

        <!--******** Header start **********-->
   @include('user.headertop')      
 
        <!--******* Header end *************-->

        <!--******* Sidebar start **********-->
     
@include('user.sidebar2')     

        <!--******** Sidebar end ***********-->

        <!-- Container starts-->
        <div class="content-body">
  <!-- row -->
  <div class="container-fluid">
    <div class="row">

<!--=====================  Page Title Start Here =====================-->
    <div class="page-titles">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href=" ">Dashboard</a>
          </li>
          <li class="breadcrumb-item active">
            <a href="">Member Registration Page</a>
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
 
   <div class="tab-content" id="tabContentMyProfileBottom">
<div class="row">


 <div class="col-xl-6">
              <div class="card">
                 <div class="card-header">
                      <h4 class="heading mb-0">Payment With Transfer</h4>
                  </div>
                  <div class="card-body">
                      <div class="alert alert-warning border-warning outline-dashed py-3 px-3 mt-1 mb-4 mb-0 text-dark d-flex align-items-center">
                          <div class="clearfix">
                              <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                  <path d="M15 30C18.9782 30 22.7936 28.4196 25.6066 25.6066C28.4196 22.7936 30 18.9782 30 15C30 11.0218 28.4196 7.20644 25.6066 4.3934C22.7936 1.58035 18.9782 0 15 0C11.0218 0 7.20644 1.58035 4.3934 4.3934C1.58035 7.20644 0 11.0218 0 15C0 18.9782 1.58035 22.7936 4.3934 25.6066C7.20644 28.4196 11.0218 30 15 30ZM12.6562 19.6875H14.0625V15.9375H12.6562C11.877 15.9375 11.25 15.3105 11.25 14.5312C11.25 13.752 11.877 13.125 12.6562 13.125H15.4688C16.248 13.125 16.875 13.752 16.875 14.5312V19.6875H17.3438C18.123 19.6875 18.75 20.3145 18.75 21.0938C18.75 21.873 18.123 22.5 17.3438 22.5H12.6562C11.877 22.5 11.25 21.873 11.25 21.0938C11.25 20.3145 11.877 19.6875 12.6562 19.6875ZM15 7.5C15.4973 7.5 15.9742 7.69754 16.3258 8.04918C16.6775 8.40081 16.875 8.87772 16.875 9.375C16.875 9.87228 16.6775 10.3492 16.3258 10.7008C15.9742 11.0525 15.4973 11.25 15 11.25C14.5027 11.25 14.0258 11.0525 13.6742 10.7008C13.3225 10.3492 13.125 9.87228 13.125 9.375C13.125 8.87772 13.3225 8.40081 13.6742 8.04918C14.0258 7.69754 14.5027 7.5 15 7.5Z" fill="#FF8A11"></path>
                              </svg>
                          </div>
                          <div class="mx-3">
                              <h6 class="mb-0 fw-semibold">We need your attention!</h6>
                              <p class="mb-0">Registration payment need to be paid before you can be verify as a member of this community.</p>
                          </div>
                      </div>
 
                      <div class="row g-3">
                          <div class="col-sm-12">
                              <div class="border border-secondary border-opacity-10 rounded p-3">
                                  <h6 class="fs-14 mb-2">REGISTRATION FEE</h6>
                                  
                                  <span class="h6 mb-0">₦{{ number_format(setting('registration_fee'), 2) }}
</span>
                              </div>
                          </div>
                          <div class="col-sm-12">
                              <div class="border border-secondary border-opacity-10 rounded p-3">
                                  <h6 class="fs-14">COMPANY ACCOUNT DETAILS</h6>
                               
                                  <span class="h6 mb-0">Account Number</span>
                                     <p class="fs-13 mb-2">2334546546</p>

                                       <span class="h6 mb-0">Account Name</span>
                                     <p class="fs-13 mb-2"> Divine Leverage Team Limited</p>

                                    <span class="h6 mb-0">Bank Name</span>
                                     <p class="fs-13 mb-2">FCMB</p>
                                     <a data-bs-toggle="modal" data-bs-target="#exampleModalCenter" class="btn btn-primary" style="margin-bottom: 20px;"><i class="fa-solid fa-list"></i> Upload Prove Of Payment</a>

             
                              </div>
                          </div>
                  </div><!-----end of row --------->

              </div>
          </div></div>







          

    
 <div class="col-xl-6">
              <div class="card">
                  <div class="card-header">
                      <h4 class="heading mb-0">Payment With Card</h4>
                  </div>
                  <div class="card-body">
                      <div class="alert alert-warning border-warning outline-dashed py-3 px-3 mt-1 mb-4 mb-0 text-dark d-flex align-items-center">
                          <div class="clearfix">
                              <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                  <path d="M15 30C18.9782 30 22.7936 28.4196 25.6066 25.6066C28.4196 22.7936 30 18.9782 30 15C30 11.0218 28.4196 7.20644 25.6066 4.3934C22.7936 1.58035 18.9782 0 15 0C11.0218 0 7.20644 1.58035 4.3934 4.3934C1.58035 7.20644 0 11.0218 0 15C0 18.9782 1.58035 22.7936 4.3934 25.6066C7.20644 28.4196 11.0218 30 15 30ZM12.6562 19.6875H14.0625V15.9375H12.6562C11.877 15.9375 11.25 15.3105 11.25 14.5312C11.25 13.752 11.877 13.125 12.6562 13.125H15.4688C16.248 13.125 16.875 13.752 16.875 14.5312V19.6875H17.3438C18.123 19.6875 18.75 20.3145 18.75 21.0938C18.75 21.873 18.123 22.5 17.3438 22.5H12.6562C11.877 22.5 11.25 21.873 11.25 21.0938C11.25 20.3145 11.877 19.6875 12.6562 19.6875ZM15 7.5C15.4973 7.5 15.9742 7.69754 16.3258 8.04918C16.6775 8.40081 16.875 8.87772 16.875 9.375C16.875 9.87228 16.6775 10.3492 16.3258 10.7008C15.9742 11.0525 15.4973 11.25 15 11.25C14.5027 11.25 14.0258 11.0525 13.6742 10.7008C13.3225 10.3492 13.125 9.87228 13.125 9.375C13.125 8.87772 13.3225 8.40081 13.6742 8.04918C14.0258 7.69754 14.5027 7.5 15 7.5Z" fill="#FF8A11"></path>
                              </svg>
                          </div>
                          <div class="mx-3">
                              <h6 class="mb-0 fw-semibold">We need your attention!</h6>
                              <p class="mb-0">Registration payment need to be paid before you can be verify as a member of this community.</p>
                          </div>
                      </div>
 
                      <div class="row g-3">
                          <div class="col-sm-12">
                              <div class="border border-secondary border-opacity-10 rounded p-3">
                                  <h6 class="fs-14 mb-2">REGISTRATION FEE</h6>
                                  
                                  <span class="h6 mb-0">₦{{ number_format(setting('registration_fee'), 2) }}
</span>
                              </div>
                          </div>


                             <div class="col-sm-6">
                              <div class="border border-secondary border-opacity-10 rounded p-3">
                                  <h6 class="fs-14">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
 </h6>
                                  <div class="d-flex align-items-center mb-3">
                                      <div class="clearfix me-2">
                                          <img src="{{ asset('images/card1.jpg') }}" alt="">
                                      </div>
                                      <div class="clearfix">
                                          <h6 class="fs-13 mb-1">Visa **** 1679</h6>
                                          <p class="fs-13 mb-0">Card expires at 00/00</p>
                                      </div>
                                  </div>
                                 
                              </div>
                          </div>



                             <div class="col-sm-6">
                              <div class="border border-secondary border-opacity-10 rounded p-3">
                                  <h6 class="fs-14">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
</h6>
                                  <div class="d-flex align-items-center mb-3">
                                      <div class="clearfix me-2">
                                          <img src="{{ asset('images/card2.jpg') }}" alt="">
                                      </div>
                                      <div class="clearfix">
                                          <h6 class="fs-13 mb-1">Mastercard **** 2704</h6>
                                          <p class="fs-13 mb-0">Card expires at 00/00</p>
                                      </div>
                                  </div>
                                  
                              </div>
                          </div>
<div class="col-sm-12 mt-20">
 

<form method="POST" action="{{ route('payment.flutterwave') }}">
    @csrf
    <button type="submit" class="btn btn-primary my-1 ms-2">Pay Online (Flutterwave)</button>
</form>

</div>

                  </div><!-----end of row --------->

              </div>
          </div></div>

          


</div><!----end of row ----->
   </div><!--------- end of tab-content --->
    </div>
  </div>
</div>



<!-- create Permission -->
              <div class="modal fade" id="exampleModalCenter">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Upload Prove Of Payment</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                   
  <form method="POST" action="{{ route('payment.bank') }}" enctype="multipart/form-data">

        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Sender Name</label>
            <input type="text" name="sendername" class="form-control" placeholder="Sender Name" required>
       <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">


        </div>

            <div class="mb-3">
            <label for="name" class="form-label">Bank Name</label>
            <input type="text" name="bank_name" class="form-control" placeholder="Bank Name" required>
        </div>

                 <div class="mb-3">
            <label for="name" class="form-label">Transaction No.</label>
            <input type="text" name="transaction_no" class="form-control" placeholder="Transaction No."  >
        </div>

          <div class="mb-3">
            <label for="name" class="form-label">Upload Receipt - (Optional)</label>
            <input type="file" name="proof" class="form-control" placeholder="Permission Name"  >
        </div>

                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary">Send Prove Of Payment</button>
                    </div>
                     </form>
                  </div>
                </div>
              </div>



        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
