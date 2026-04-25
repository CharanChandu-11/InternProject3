{{-- resources/views/super-admin/students/id-card.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ID Card - {{ $student->user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .id-card {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px;
            border-radius: 10px;
            color: white;
        }
        .id-card-inner {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 15px;
        }
        .school-name {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .photo {
            text-align: center;
            margin-bottom: 10px;
        }
        .photo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid white;
        }
        .details {
            font-size: 11px;
        }
        .details table {
            width: 100%;
        }
        .details td {
            padding: 3px 0;
        }
        .label {
            font-weight: bold;
            width: 40%;
        }
        .signature {
            margin-top: 10px;
            text-align: center;
            font-size: 10px;
            border-top: 1px dashed rgba(255,255,255,0.3);
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="id-card">
        <div class="id-card-inner">
            <div class="school-name">
                {{ $school->school_name ?? 'Smart School' }}
            </div>
            <div class="photo">
                <img src="{{ public_path('storage/' . $student->user->profile_photo) }}" alt="Photo">
            </div>
            <div class="details">
                <table>
                    <tr><td class="label">Name</td><td>{{ $student->user->name }}</td></tr>
                    <tr><td class="label">Admission No</td><td>{{ $student->admission_number }}</td></tr>
                    <tr><td class="label">Class</td><td>{{ $student->class->name }}</td></tr>
                    <tr><td class="label">Section</td><td>{{ $student->section->name }}</td></tr>
                    <tr><td class="label">Roll No</td><td>{{ $student->roll_number }}</td></tr>
                    <tr><td class="label">DOB</td><td>{{ $student->user->profile?->date_of_birth?->format('d-m-Y') }}</td></tr>
                    <tr><td class="label">Blood Group</td><td>{{ $student->user->profile?->blood_group ?? 'N/A' }}</td></tr>
                    <tr><td class="label">Address</td><td>{{ $student->user->address ?? 'N/A' }}</td></tr>
                </table>
            </div>
            <div class="signature">
                Authorized Signature
            </div>
        </div>
    </div>
</body>
</html>