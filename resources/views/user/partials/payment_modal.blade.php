<div class="modal fade" id="exampleModalCenter">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Proof Of Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('payment.bank') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                    <div class="mb-3">
                        <label class="form-label">Sender Name</label>
                        <input type="text" name="sendername" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction No.</label>
                        <input type="text" name="transaction_no" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Receipt (Optional)</label>
                        <input type="file" name="proof" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Send Proof Of Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
