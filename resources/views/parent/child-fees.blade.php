{{-- resources/views/parent/child-fees.blade.php --}}
@extends('layouts.parent')

@section('title', $student->user->name . ' - Fees')

@section('content')
<div class="animate-fadeInUp">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-rupee-sign me-2"></i> 
                            Fee Details - {{ $student->user->name }}
                        </div>
                        <a href="{{ route('parent.children.show', $student) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($fees->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr class="bg-light">
                                        <th>Category</th>
                                        <th>Total Amount</th>
                                        <th>Paid Amount</th>
                                        <th>Due Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                     </tr>
                                </thead>
                                <tbody>
                                    @foreach($fees as $fee)
                                        <tr>
                                            <td class="fw-bold">{{ $fee->feeStructure->feeCategory->name }}</td>
                                            <td>₹{{ number_format($fee->total_amount, 2) }}</td>
                                            <td class="text-success">₹{{ number_format($fee->paid_amount, 2) }}</td>
                                            <td class="fw-bold text-{{ $fee->due_amount > 0 ? 'danger' : 'success' }}">
                                                ₹{{ number_format($fee->due_amount, 2) }}
                                            </td>
                                            <td>{{ $fee->due_date->format('d M, Y') }}</td>
                                            <td>
                                                @if($fee->status == 'paid')
                                                    <span class="badge bg-success">Paid</span>
                                                @elseif($fee->status == 'partial')
                                                    <span class="badge bg-warning">Partial</span>
                                                @elseif($fee->due_date->isPast())
                                                    <span class="badge bg-danger">Overdue</span>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr class="fw-bold">
                                        <td>Total</td>
                                        <td>₹{{ number_format($totalFees, 2) }}</td>
                                        <td class="text-success">₹{{ number_format($totalPaid, 2) }}</td>
                                        <td class="text-danger">₹{{ number_format($totalDue, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No fee records found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i> Payment History
                </div>
                <div class="card-body">
                    @if($payments->count() > 0)
                        @foreach($payments->take(5) as $payment)
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div>
                                    <div class="fw-bold">₹{{ number_format($payment->amount, 2) }}</div>
                                    <small class="text-muted">{{ $payment->payment_date->format('d M, Y') }}</small>
                                </div>
                                <div>
                                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                    <a href="{{ route('parent.payments.receipt', $payment) }}" class="btn btn-sm btn-link" target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">No payment history.</p>
                    @endif
                </div>
            </div>
            
            @if($totalDue > 0)
                <div class="card mt-4 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%);">
                    <div class="card-body text-center">
                        <i class="fas fa-rupee-sign fa-3x mb-3 opacity-75"></i>
                        <h3 class="mb-2">₹{{ number_format($totalDue, 2) }}</h3>
                        <p class="mb-3">Total Due Amount</p>
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#payModal">
                            <i class="fas fa-credit-card me-2"></i> Pay Now
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Make Payment - {{ $student->user->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="#" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" value="{{ $totalDue }}" step="0.01" min="1" max="{{ $totalDue }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">Select Method</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="upi">UPI</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Proceed to Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection