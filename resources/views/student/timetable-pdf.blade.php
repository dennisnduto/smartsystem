<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Timetable - {{ $user->name }}</title>
    <style>
        @page {
            margin: 1cm;
            size: A4 portrait;
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            margin: 0;
            color: #1e293b;
            line-height: 1.5;
        }
        .header {
            margin-bottom: 25px;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 15px;
        }
        .header-top {
            width: 100%;
            margin-bottom: 10px;
        }
        .logo-txt {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
            letter-spacing: -1px;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .student-info {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 12px;
            border: 1px solid #e2e8f0;
        }
        .student-info table {
            width: 100%;
        }
        .student-info td {
            padding: 2px 0;
        }
        h2 { 
            color: #1e293b; 
            font-size: 16px;
            margin-top: 25px;
            margin-bottom: 12px;
            padding-left: 10px;
            border-left: 4px solid #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        table.timetable { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        table.timetable th, table.timetable td { 
            border: 1px solid #e2e8f0; 
            padding: 12px; 
            text-align: left; 
            font-size: 11px;
            vertical-align: top;
        }
        table.timetable th { 
            background-color: #4f46e5; 
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        .time-col {
            background-color: #f1f5f9;
            font-weight: bold;
            width: 110px;
            color: #475569;
        }
        .entry-box {
            background-color: #f0f9ff;
            border-left: 3px solid #0ea5e9;
            padding: 8px;
            margin-bottom: 5px;
            border-radius: 4px;
        }
        .unit-code { font-weight: bold; color: #4f46e5; font-size: 12px; }
        .unit-name { font-size: 10px; color: #64748b; margin-top: 2px; }
        .room-tag { 
            background-color: #ecfdf5; 
            color: #059669; 
            padding: 1px 5px; 
            border-radius: 4px; 
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #d1fae5;
            display: inline-block;
            margin-top: 4px;
        }
        .lecturer { font-size: 9px; color: #475569; margin-top: 4px; font-style: italic; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
        .empty-cell { color: #cbd5e1; font-style: italic; }
        .summary-box {
            margin-top: 30px;
            padding: 15px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }
        .summary-title {
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-txt">{{ strtoupper($user->institution->name ?? 'SMART SYSTEM') }}</div>
        <div class="report-title">Personal Academic Schedule</div>
    </div>

    <div class="student-info">
        <table border="0">
            <tr>
                <td width="15%"><strong>Full Name:</strong></td>
                <td width="45%">{{ $user->name }}</td>
                <td width="15%"><strong>Academic Year:</strong></td>
                <td width="25%">{{ $user->year_of_study ?? 'Not Set' }}</td>
            </tr>
            <tr>
                <td><strong>Email ID:</strong></td>
                <td>{{ $user->email }}</td>
                <td><strong>Generated:</strong></td>
                <td>{{ now()->format('D, M j, Y | H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Institution:</strong></td>
                <td colspan="3">{{ $user->institution->name ?? 'SMART University' }}</td>
            </tr>
        </table>
    </div>

    @if($entries->isEmpty())
        <div style="text-align: center; padding: 50px; background: #f8fafc; border-radius: 15px; border: 2px dashed #e2e8f0;">
            <p style="font-size: 18px; color: #64748b;">No schedule entries found for your profile.</p>
        </div>
    @else
        <table class="timetable">
            <thead>
                <tr>
                    <th class="time-col">Time Period</th>
                    @foreach($days as $dayNum => $dayName)
                        <th>{{ strtoupper($dayName) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($timeSlots as $slotNum => $timeLabel)
                    <tr>
                        <td class="time-col">{{ $timeLabel }}</td>
                        @foreach($days as $dayNum => $dayName)
                            @php 
                                $cellEntries = ($entriesByDay[$dayNum] ?? collect())->filter(fn($e) => (int)$e->slot === $slotNum); 
                            @endphp
                            <td>
                                @forelse($cellEntries as $entry)
                                    <div class="entry-box">
                                        <div class="unit-code">{{ optional($entry->unit)->code ?? '—' }}</div>
                                        <div class="unit-name">{{ optional($entry->unit)->name ?? 'No Name' }}</div>
                                        <div class="room-tag">{{ optional($entry->room)->name ?? 'TBA' }}</div>
                                        <div class="lecturer">Facilitator: {{ optional($entry->lecturer)->name ?? 'TBA' }}</div>
                                    </div>
                                @empty
                                    <div class="empty-cell">—</div>
                                @endforelse
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-title">Weekly Load Summary</div>
            <table border="0" style="width: 100%; font-size: 11px;">
                <tr>
                    <td width="25%"><strong>Total Contact Hours:</strong> {{ $entries->count() * 3 }} hrs</td>
                    <td width="25%"><strong>Active Courses:</strong> {{ $entries->pluck('course_id')->unique()->count() }}</td>
                    <td width="25%"><strong>Total Units:</strong> {{ $entries->pluck('unit_id')->unique()->count() }}</td>
                    <td width="25%"><strong>Total Sessions:</strong> {{ $entries->count() }}</td>
                </tr>
            </table>
        </div>
    @endif

    <div class="footer">
        © {{ date('Y') }} SMART Academic Management • Official Student Schedule • Verified Published Data
    </div>
</body>
</html>
