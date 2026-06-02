@extends('layouts.app')
@section('content')

<style>

.stat-card{
    background:#fff;
    border-radius:8px;
    padding:10px;
    display:flex;
    align-items:center;
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
}

.stat-icon{
    width:25px;
    height:25px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:13px;
    margin-right:15px;
}

.success-bg{
    background:#e9f9f0;
    color:#28a745;
}

.warning-bg{
    background:#fff4e5;
    color:#f39c12;
}

.stat-title{
    font-size:10px;
    color:#888;
    margin-bottom:3px;
}

.stat-value{
    font-size:12px;
    font-weight:600;
}

.success-text{
    color:#28a745;
}

.warning-text{
    color:#f39c12;
}

</style>

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
            <a href="">Payout</a>
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
 
<div class="row g-3" style="margin-bottom: 30px;">

  <div class="col-lg-2 col-md-6 col-12">
    <div class="stat-card">
      <div class="stat-icon success-bg">✔</div>
      <div>
        <div class="stat-title">Total Withdrawals</div>
        <div class="stat-value success-text">{{ $totalWithdrawals }}</div>
      </div>
    </div>
  </div>

  <div class="col-lg-2 col-md-6 col-12">
    <div class="stat-card">
      <div class="stat-icon success-bg">✔</div>
      <div>
        <div class="stat-title">Total Paid</div>
        <div class="stat-value success-text">{{ $totalPaid }}</div>
      </div>
    </div>
  </div>

  <div class="col-lg-2 col-md-6 col-12">
    <div class="stat-card">
      <div class="stat-icon warning-bg">⚠</div>
      <div>
        <div class="stat-title">Total Pending</div>
        <div class="stat-value warning-text">{{ $totalPending }}</div>
      </div>
    </div>
  </div>

    <div class="col-lg-2 col-md-6 col-12">
    <div class="stat-card">
      <div class="stat-icon warning-bg">⚠</div>
      <div>
        <div class="stat-title">Total Declined</div>
        <div class="stat-value warning-text">{{ $totalDeclined }}</div>
      </div>
    </div>
  </div>

  <div class="col-lg-2 col-md-6 col-12">
    <div class="stat-card">
      <div class="stat-icon success-bg">✔</div>
      <div>
        <div class="stat-title">Total Amount Paid</div>
        <div class="stat-value success-text">₦ {{ number_format($totalAmountPaid, 2) }}</div>
      </div>
    </div>
  </div>

  <div class="col-lg-2 col-md-6 col-12">
    <div class="stat-card">
      <div class="stat-icon warning-bg">⚠</div>
      <div>
        <div class="stat-title">Total Amount Pending</div>
        <div class="stat-value warning-text">₦ {{ number_format($totalAmountPending, 2) }}</div>
      </div>
    </div>
  </div>

</div><!-- end of row -->



<style>
.table thead th{
    font-size:10px;
}
    </style>


    <form id="payoutForm" method="POST">
        @csrf
        <table class="table table-responsive-md">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>S/N</th>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Payout Fee</th>
                    <th>Amount to receive</th>
                    <th>Bank Details</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Approve</th>
                    <th>Decline</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $data)
                <tr>
                    <td><input type="checkbox" name="payout_ids[]" value="{{ $data->id }}"></td>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $data->user->first_name ?? 'N/A' }} {{ $data->user->last_name ?? 'N/A' }}</td>
                    <td>₦{{ number_format($data->amount,2) }}</td>
                    <td>₦{{ number_format($data->withdrawal_fee,2) }}</td>
                    <td>₦{{ number_format($data->amount_payable,2) }}</td>
                    <td>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#exampleModalCenter{{ $data->id }}">
                            View Detail
                        </a>
                    </td>
                    <td>{{ $data->status }}</td>
                    <td>{{ $data->type }}</td>
                    <td>{{ $data->created_at->format('F j, Y') }}</td>

                    <!-- Approve -->
                    <td>
                        @if($data->status == 'pending')
                        <button type="button" class="btn btn-success"
                            onclick="submitPayoutForm('{{ route('superadmin.payout.approvePayout',$data->id) }}')">
                            Approve
                        </button>
                        @endif
                    </td>

                    <!-- Decline -->
                    <td>
                        <button type="button" class="btn btn-warning"
                            data-bs-toggle="modal"
                            data-bs-target="#declinePayoutModal{{ $data->id }}">
                            Decline
                        </button>
                    </td>

                    <!-- Delete -->
                    <td>
                        <button type="button" class="btn btn-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deletePayoutModal{{ $data->id }}">
                            Delete
                        </button>
                    </td>

 <!-- Modal -->
      <div class="modal fade" id="exampleModalCenter{{ $data->id }}">
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

               
          </div>
        </div>
      </div>


                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Bulk Approve -->
        <button type="button" id="bulkApproveBtn" class="btn btn-primary mt-3" disabled
            onclick="submitPayoutForm('{{ route('superadmin.payout.bulkApprove') }}')">
            Bulk Approval
        </button>
    </form>

    <!-- ============================= -->
    <!-- MODALS (OUTSIDE MAIN FORM) -->
    <!-- ============================= -->
    @foreach($transactions as $data)

    <!-- View Bank Details Modal -->
    <div class="modal fade" id="exampleModalCenter{{ $data->id }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>{{ $data->user->first_name ?? 'N/A' }} {{ $data->user->last_name ?? 'N/A' }} - Bank Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Account Name:</strong> {{ $data->user->account_name }}</p>
                    <p><strong>Account Number:</strong> {{ $data->user->account_no }}</p>
                    <p><strong>Bank Name:</strong> {{ $data->user->bank_name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Decline Modal -->
    <div class="modal fade" id="declinePayoutModal{{ $data->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Decline Payout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to decline this payout?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('superadmin.payout.decline',$data->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-warning">Decline Payout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deletePayoutModal{{ $data->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Delete Payout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this payout? Once deleted, you cannot recover it.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('superadmin.payout.delete',$data->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Payout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @endforeach
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const selectAll = document.getElementById("selectAll");
    const checkboxes = document.querySelectorAll('input[name="payout_ids[]"]');
    const bulkBtn = document.getElementById("bulkApproveBtn");

    function toggleBulkButton() {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        bulkBtn.disabled = !anyChecked;
    }

    selectAll.addEventListener("change", function () {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        toggleBulkButton();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener("change", function () {
            if (!cb.checked) {
                selectAll.checked = false;
            } else if (Array.from(checkboxes).every(c => c.checked)) {
                selectAll.checked = true;
            }
            toggleBulkButton();
        });
    });
});

// ✅ Dynamic form submission
function submitPayoutForm(actionUrl) {
    const form = document.getElementById('payoutForm');
    form.action = actionUrl;
    form.submit();
}
</script>

  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
