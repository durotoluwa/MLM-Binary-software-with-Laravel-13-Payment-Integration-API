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
            <a href="">Pending Withdrawal</a>
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


<div class="table-responsive">
       <table class="table table-responsive-md">
                <thead>
                  <tr>
                    <th>S/N</th>
                     <th>Full Name</th>
                     <th>Amount</th>
                  
                    <th>Bank Details</th>
                    
                     
                    <th>Submitted At</th>
                    <th> </th>
                    <th> </th>
                        
                  </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                      @foreach($transactions as $data)
                  <tr>
                       <td>{{ $no }}</td>
                <td>{{ $data->user->first_name ?? 'N/A' }} {{ $data->user->last_name ?? 'N/A' }}</td>
                  <td>₦{{ number_format($data->amount, 2) }}
</td>
                        <td><button data-bs-toggle="modal" data-bs-target="#exampleModalCenter"  class="btn btn-rounded btn-outline-primary">View Bank Details</button>
</td>
                        
                        
                         <td>{{ $data->created_at->format('F j, Y \a\t g:i A') }}</td>
                    <td width="5%">

   <form action="{{ route('superadmin.transaction.approveWithdrawal', $data->id) }}" method="POST" onsubmit="return confirm('Approve this payment?');">
                                @csrf
                        <button type="submit"  class="btn btn-rounded btn-outline-primary">Approve</button>
                            </form>
                    </td>
                       <td width="5%">    
<button data-bs-toggle="modal" data-bs-target="#basicModal{{ $data->id }}"  type="button" class="btn btn-rounded btn-outline-danger"><i class="fa-solid fa-trash"></i> Delete</button>        
                    </td>
    
 <!-- Modal -->
      <div class="modal fade" id="exampleModalCenter">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ $data->user->first_name ?? 'N/A' }} {{ $data->user->last_name ?? 'N/A' }} - Bank Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
       
                <p><strong>Account Name:</strong> <span  style="color: black; font-size:18px; font-weight:800;">{{ $data->user->account_name }}</span></p>
                <p><strong>Account Number:</strong> <span  style="color: black; font-size:18px; font-weight:800;"> {{ $data->user->account_no }}</span></p>
                <p><strong>Bank Name:</strong> <span  style="color: black; font-size:18px; font-weight:800;"> {{ $data->user->bank_name }}</span></p>

                   <form action="{{ route('superadmin.transaction.approveWithdrawal', $data->id) }}" method="POST" onsubmit="return confirm('Approve this payment?');">
                                @csrf
                        <button type="submit"  class="btn btn-rounded btn-outline-primary">Approve Withdrawal</button>
                            </form>
          </div>
        </div>
      </div>

                  </tr>

    
             
    <?php $no++; ?>
 @endforeach
</tbody></table>
          

 

</div>
</div></div></div></div><!-----end of row----->
    



  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
