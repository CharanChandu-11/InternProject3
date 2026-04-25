{{-- resources/views/admin/timetable/export.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Timetable - {{ $class->name }} Section {{ $section->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .time-slot {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .subject {
            font-weight: bold;
            color: #007bff;
        }
        .teacher {
            font-size: 10px;
            color: #666;
        }
        .room {
            font-size: 9px;
            color: #999;
        }
        .break {
            color: #999;
            font-style: italic;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school->school_name ?? 'Smart School ERP' }}</h1>
        <p>{{ $school->address ?? '' }}</p>
        <p>Phone: {{ $school->phone ?? '' }} | Email: {{ $school->email ?? '' }}</p>
        <h3>Timetable - {{ $class->name }} Section {{ $section->name }}</h3>
        <p>Academic Year: {{ date('Y') }} - {{ date('Y') + 1 }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Time / Day</th>
                @foreach($days as $day)
                    <th>{{ ucfirst($day) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($timeSlots as $slot)
                <tr>
                    <td class="time-slot">
                        {{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} - 
                        {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}
                        @if($slot->is_break)
                            <br><span class="break">(Break)</span>
                        @endif
                    </td>
                    @foreach($days as $day)
                        <td>
                            @php $cell = $timetable[$day]['slots'][$loop->parent->index] ?? null; @endphp
                            @if($slot->is_break)
                                <span class="break">Break</span>
                            @elseif($cell && $cell['subject'])
                                <div class="subject">{{ $cell['subject']->name ?? '' }}</div>
                                <div class="teacher">{{ $cell['teacher']->name ?? '' }}</div>
                                <div class="room">Room: {{ $cell['room_number'] ?? 'N/A' }}</div>
                            @else
                                <span class="break">-</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>Generated on {{ now()->format('F j, Y h:i A') }}</p>
        <p>This is a system-generated timetable. Please contact the admin for any discrepancies.</p>
    </div>
</body>
</html>