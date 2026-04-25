{{-- resources/views/student/fees/receipt.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $payment->payment_number }}</title>
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
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .receipt-title {
            font-size: 18px;
            margin-top: 10px;
        }
        .receipt-no {
            text-align: right;
            font-size: 12px;
            margin-bottom: 20px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .details-table td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .amount {
            font-size: 16px;
            font-weight: bold;
            color: green;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .signature {
            margin-top: 30px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="school-name">{{ \App\Models\SchoolSetting::first()->school_name ?? 'Smart School' }}</div>
            <div>{{ \App\Models\SchoolSetting::first()->address ?? '' }}</div>
            <div class="receipt-title">PAYMENT RECEIPT</div>
        </div>
        
        <div class="receipt-no">
            Receipt No: {{ $payment->payment_number }} | Date: {{ $payment->payment_date->format('d-m-Y H:i:s') }}
        </div>
        
        <table class="details-table">
            <tr>
                <td>Student Name:</td>
                <td>{{ $payment->student->user->name }}</td>
            </tr>
            <tr>
                <td>Admission Number:</td>
                <td>{{ $payment->student->admission_number }}</td>
            </tr>
            <tr>
                <td>Class:</td>
                <td>{{ $payment->student->class->name ?? 'N/A' }} - {{ $payment->student->section->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Fee Category:</td>
                <td>{{ $payment->studentFee->feeStructure->feeCategory->name }}</td>
            </tr>
            <tr>
                <td>Amount Paid:</td>
                <td class="amount">₹ {{ number_format($payment->amount, 2) }}</td>
            </tr>
            <tr>
                <td>Payment Method:</td>
                <td>{{ ucfirst($payment->payment_method) }}</td>
            </tr>
            @if($payment->transaction_id)
            <tr>
                <td>Transaction ID:</td>
                <td>{{ $payment->transaction_id }}</td>
            </tr>
            @endif
            <tr>
                <td>Payment Status:</td>
                <td><strong>Completed</strong></td>
            </tr>
        </table>
        
        <div class="signature">
            Authorized Signature
        </div>
        
        <div class="footer">
            This is a computer generated receipt. No signature required.
        </div>
    </div>
</body>
</html>