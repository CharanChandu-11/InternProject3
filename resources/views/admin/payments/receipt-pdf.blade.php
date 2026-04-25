{{-- resources/views/admin/payments/receipt-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="school-name">{{ config('app.name', 'Smart School') }}</div>
            <div>{{ $payment->student->class->name ?? '' }} - {{ $payment->student->section->name ?? '' }}</div>
        </div>
        
        <div class="receipt-title">PAYMENT RECEIPT</div>
        
        <div class="info-row">
            <span class="info-label">Receipt No:</span>
            <span>{{ $payment->payment_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span>{{ $payment->payment_date->format('d F Y') }}</span>
        </div>
        
        <h3>Student Details</h3>
        <div class="info-row">
            <span class="info-label">Student Name:</span>
            <span>{{ $payment->student->user->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Admission No:</span>
            <span>{{ $payment->student->admission_number ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Class/Section:</span>
            <span>{{ $payment->student->class->name ?? 'N/A' }} - {{ $payment->student->section->name ?? 'N/A' }}</span>
        </div>
        
        <h3>Payment Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @if($payment->studentFee)
                <tr>
                    <td>{{ $payment->studentFee->feeStructure->feeCategory->name ?? 'Fee Payment' }}</td>
                    <td>{{ number_format($payment->amount, 2) }}</td>
                </tr>
                @else
                <tr>
                    <td>Fee Payment</td>
                    <td>{{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>
        
        <div class="total">
            Total Amount: ₹ {{ number_format($payment->amount, 2) }}
        </div>
        
        <div class="info-row">
            <span class="info-label">Payment Method:</span>
            <span>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
        </div>
        @if($payment->transaction_id)
        <div class="info-row">
            <span class="info-label">Transaction ID:</span>
            <span>{{ $payment->transaction_id }}</span>
        </div>
        @endif
        @if($payment->remarks)
        <div class="info-row">
            <span class="info-label">Remarks:</span>
            <span>{{ $payment->remarks }}</span>
        </div>
        @endif
        
        <div class="footer">
            This is a computer-generated receipt and does not require a signature.<br>
            Thank you for your payment!
        </div>
    </div>
</body>
</html>