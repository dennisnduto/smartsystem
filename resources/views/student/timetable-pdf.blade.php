<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Timetable - {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        .timetable {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .timetable th,
        .timetable td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .timetable th {
            background-color: #f8fafc;
            font-weight: bold;
            color: #374151;
        }
        .time-column {
            background-color: #f1f5f9;
            font-weight: bold;
            width: 120px;
        }
        .entry {
            background-color: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 4px;
            padding: 6px;
            margin: 2px 0;
            font-size: 11px;
        }
        .unit-code {
            font-weight: bold;
            color: #1e40af;
        }
        .unit-name {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .course-name {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .lecturer {
            font-size: 9px;
            color: #374151;
            margin-top: 2px;
        }
        .room {
            background-color: #ecfdf5;
            color: #065f46;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
            margin-top: 2px;
        }
        .summary {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8fafc;
            border-radius: 8px;
        }
        .summary h3 {
            color: #1e40af;
            margin-top: 0;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .summary-item {
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        .summary-item h4 {
            margin: 0 0 5px 0;
            color: #64748b;
            font-size: 12px;
        }
        .summary-item p {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
        }
        .empty-cell {
            color: #cbd5e1;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $user->institution->name ?? 'University Timetable' }}</h1>
        <p><strong>Student Timetable</strong></p>
        <p>{{ $user->name }} ({{ $user->email }})</p>
        <p>Generated on {{ now()->format('F d, Y') }}</p>
    </div>

    @if($entries->isEmpty())
        <div style="text-align: center; padding: 40px; color: #666;">
            <h2>No Schedule Available</h2>
            <p>No timetable entries found for your courses.</p>
        </div>
    @else
        <table class="timetable">
            <thead>
                <tr>
                    <th class="time-column">Time</th>
                    <th>Monday</th>
                    <th>Tuesday</th>
                    <th>Wednesday</th>
                    <th>Thursday</th>
                    <th>Friday</th>
                </tr>
            </thead>
            <tbody>
                @foreach($timeSlots as $slotNum => $timeLabel)
                    <tr>
                        <td class="time-column">{{ $timeLabel }}</td>
                        @foreach($days as $dayNum => $dayName)
                            @php 
                                $cell = ($entriesByDay[$dayNum] ?? collect())->filter(fn($e) => (int)$e->slot === $slotNum); 
                            @endphp
                            <td>
                                @forelse($cell as $entry)
                                    <div class="entry">
                                        <div class="unit-code">{{ optional($entry->unit)->code ?? '—' }}</div>
                                        @if($entry->unit && $entry->unit->name)
                                            <div class="unit-name">{{ $entry->unit->name }}</div>
                                        @endif
                                        @if($entry->course && $entry->course->name)
                                            <div class="course-name">{{ $entry->course->name }}</div>
                                        @endif
                                        @if($entry->room && $entry->room->name)
                                            <span class="room">{{ $entry->room->name }}</span>
                                        @endif
                                        @if($entry->lecturer && $entry->lecturer->name)
                                            <div class="lecturer">Lecturer: {{ $entry->lecturer->name }}</div>
                                        @endif
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

        <div class="summary">
            <h3>Timetable Summary</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <h4>Total Classes</h4>
                    <p>{{ $entries->count() }}</p>
                </div>
                <div class="summary-item">
                    <h4>Units</h4>
                    <p>{{ $entries->pluck('unit_id')->unique()->count() }}</p>
                </div>
                <div class="summary-item">
                    <h4>Rooms Used</h4>
                    <p>{{ $entries->pluck('room_id')->filter()->unique()->count() }}</p>
                </div>
                <div class="summary-item">
                    <h4>Courses</h4>
                    <p>{{ $entries->pluck('course_id')->filter()->unique()->count() }}</p>
                </div>
            </div>
        </div>
    @endif
</body>
</html>
