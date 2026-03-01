<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Timetable for {{ $user->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; text-align: center; }
        h2 { color: #666; border-bottom: 2px solid #eee; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .class-info { font-size: 0.9em; color: #666; }
        .room-info { color: #0066cc; font-weight: bold; }
        .no-classes { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <h1>Lecturer Timetable</h1>
    <p><strong>Name:</strong> {{ $user->name }}</p>
    <p><strong>Generated:</strong> {{ now()->format('Y-m-d H:i') }}</p>
    <hr>

    @php
        $timeSlots = [1=>'7:00-10:00',2=>'10:00-13:00',3=>'13:00-16:00',4=>'16:00-19:00'];
        $dayNames = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
    @endphp

    @foreach($entriesByDay as $dayNum => $dayEntries)
        <h2>{{ $dayNames[$dayNum] ?? 'Day ' . $dayNum }}</h2>
        
        @if($dayEntries->isEmpty())
            <p class="no-classes">No classes scheduled</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Unit Code</th>
                        <th>Unit Name</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dayEntries as $entry)
                        <tr>
                            <td>{{ $timeSlots[$entry->slot] ?? 'Slot ' . $entry->slot }}</td>
                            <td>{{ $entry->unit->code ?? '—' }}</td>
                            <td>{{ $entry->unit->name ?? '—' }}</td>
                            <td>{{ $entry->course->name ?? '—' }}</td>
                            <td>{{ app(\App\Http\Controllers\Lecturer\SelfServiceController::class)->getYearOfStudy($entry) ?? '—' }}</td>
                            <td class="room-info">{{ $entry->room->name ?? 'TBA' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    @if($entries->isEmpty())
        <p class="no-classes">No classes scheduled this week.</p>
    @endif

    <hr>
    <p><small>Total classes this week: {{ $entries->count() }}</small></p>
</body>
</html>
