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
            <a href="">Payout</a>
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
    <form method="POST" action="{{ route('superadmin.payout.bulkApprove') }}">
    @csrf
       <table class="table table-responsive-md">
                <thead>
                  <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>S/N</th>
                     <th>Name</th>
                     <th>Amount</th>
                      <th>Payout Fee</th>
                      <th>Payout Amt.</th>
                    <th>Bank Details</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>date</th>
                    <th> </th>
                    <th> </th>
                        <th> </th>
                  </tr>
                </thead>
                <tbody>
                    
                    <?php $no = 1; ?>
                      @foreach($transactions as $data)
                  <tr>
                    <td><input type="checkbox" name="payout_ids[]" value="{{ $data->id }}"></td>
                       <td>{{ $no }}</td>
                <td>{{ $data->user->first_name ?? 'N/A' }} {{ $data->user->last_name ?? 'N/A' }}</td>
                  <td>₦{{ number_format($data->amount, 2) }}</td>
                  <td>₦{{ number_format($data->withdrawal_fee, 2) }}</td>
                  <td>₦{{ number_format($data->amount_payable, 2) }}</td>
                        <td><button data-bs-toggle="modal" data-bs-target="#exampleModalCenter{{ $data->id }}"  class="btn btn-rounded btn-outline-primary">View Bank Details</button>
</td>
<td>{{ $data->status }}</td>
<td>{{ $data->type }}</td>
                        
                        
<td>{{ $data->created_at->format('F j, Y \a\t g:i A') }}</td>
                    <td width="5%">

   <form method="POST" action="{{ route('superadmin.payout.approvePayout', $data->id) }}">
    @csrf
    <button class="btn btn-success">Approve</button>
</form>
                    </td>
<td width="5%">
 <button data-bs-toggle="modal" data-bs-target="#declinePayoutModal{{ $data->id }}"  class="btn btn-warning">Decline</button>
  </td>
<td width="5%">
    <button data-bs-toggle="modal" data-bs-target="#deletePayoutModal{{ $data->id }}"  class="btn btn-danger">Delete</button>
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

    <!--==== Delete Payout Modal =======-->
<div class="modal fade" id="deletePayoutModal{{ $data->id }}" tabindex="-1" role="dialog" aria-labelledby="deletePayoutLabel{{ $data->id }}" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="deletePayoutLabel{{ $data->id }}">Delete Payout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        Are you sure you want to delete this payout? Once deleted, you cannot recover it.
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

        <form method="POST" action="{{ route('superadmin.payout.delete', $data->id) }}">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger show_confirm" data-toggle="tooltip" title="Delete Payout">
            Delete Payout
          </button>
        </form>
      </div>

    </div>
  </div>
</div>


<!--==== Decline Payout Modal =======-->
<div class="modal fade" id="declinePayoutModal{{ $data->id }}" tabindex="-1" role="dialog" aria-labelledby="declinePayoutLabel{{ $data->id }}" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="declinePayoutLabel{{ $data->id }}">Decline Payout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        Are you sure you want to decline this payout? The user’s withdrawal balance will remain unchanged and they can request again later.
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

        <form method="POST" action="{{ route('superadmin.payout.decline', $data->id) }}">
          @csrf
          <button type="submit" class="btn btn-warning show_confirm" data-toggle="tooltip" title="Decline Payout">
            Decline Payout
          </button>
        </form>
      </div>

    </div>
  </div>
</div>

             
    <?php $no++; ?>
 @endforeach
</tbody></table>
 <!-- Bulk Approve Button -->
       <button type="submit" id="bulkApproveBtn" class="btn btn-primary mt-3" disabled>Bulk Approval</button>

    </form>
          

 

<script>
document.addEventListener("DOMContentLoaded", function () {
    const selectAll = document.getElementById("selectAll");
    const checkboxes = document.querySelectorAll('input[name="payout_ids[]"]');
    const bulkBtn = document.getElementById("bulkApproveBtn");

    function toggleBulkButton() {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        bulkBtn.disabled = !anyChecked;
    }

    // Select All toggle
    selectAll.addEventListener("change", function () {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        toggleBulkButton();
    });

    // Individual checkbox toggle
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
</script>



</div>
</div></div></div></div><!-----end of row----->
    



  </div>
</div>

        <!-- Container Ends-->

@include('layouts.footer_content')



@endsection
