<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $timetable->name }} - Timetable</title>
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
            padding: 4px;
            margin: 2px 0;
            font-size: 12px;
        }
        .unit-code {
            font-weight: bold;
            color: #1e40af;
        }
        .course-name {
            font-size: 10px;
            color: #64748b;
        }
        .lecturer {
            font-size: 10px;
            color: #374151;
        }
        .room {
            background-color: #ecfdf5;
            color: #065f46;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 10px;
            font-weight: bold;
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
            margin: 0 0 10px 0;
            color: #374151;
            font-size: 14px;
        }
        .summary-item .number {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .course-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        .course-header {
            background-color: #6366f1;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .course-header h2 {
            margin: 0;
            font-size: 20px;
        }
        .course-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        @media print {
            body { margin: 0; padding: 10px; }
            .course-section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $timetable->institution->name ?? 'University Timetable' }}</h1>
        <p>{{ $timetable->name }}</p>
        @if(isset($courseName) && $courseName)
            <p><strong>Course:</strong> {{ $courseName }}</p>
        @endif
        <p>{{ $timetable->academic_year ?? '—' }} • {{ $timetable->semester ?? 'N/A' }}</p>
    </div>

    @if($timetable->entries->isEmpty())
        <div style="text-align: center; padding: 40px; color: #666;">
            <h2>No Schedule Available</h2>
            <p>No entries have been generated for this timetable yet.</p>
        </div>
    @else
        @php
            // Group entries by course for better organization
            $entriesByCourse = $timetable->entries->groupBy('course_id');
            $courses = $entriesByCourse->map(function($entries, $courseId) {
                $firstEntry = $entries->first();
                return [
                    'id' => $courseId,
                    'name' => $firstEntry->course->name ?? 'Unknown Course',
                    'entries' => $entries
                ];
            })->sortBy('name');
        @endphp

        @if(isset($courseName) && $courseName)
            {{-- Single course export --}}
            @php $course = $courses->first(); @endphp
            @if($course)
                <div class="course-section">
                    <div class="course-header">
                        <h2>{{ $course['name'] }}</h2>
                        <p>{{ $course['entries']->count() }} units scheduled</p>
                    </div>

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
                            @php
                                $timeSlots = [
                                    1 => '7:00 AM - 10:00 AM',
                                    2 => '10:00 AM - 1:00 PM',
                                    3 => '1:00 PM - 4:00 PM',
                                    4 => '4:00 PM - 7:00 PM'
                                ];
                                $dayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
                            @endphp
                            
                            @foreach($timeSlots as $slotNum => $timeLabel)
                                <tr>
                                    <td class="time-column">{{ $timeLabel }}</td>
                                    @foreach($dayNames as $dayNum => $dayName)
                                        @php
                                            $courseEntries = $course['entries']->filter(function ($entry) use ($dayNum, $slotNum) {
                                                return (int)$entry->day_of_week === (int)$dayNum && (int)$entry->slot === (int)$slotNum;
                                            });
                                        @endphp
                                        <td>
                                            @foreach($courseEntries as $entry)
                                                <div class="entry">
                                                    <div class="unit-code">{{ $entry->unit->code ?? 'N/A' }}</div>
                                                    @if($entry->course)
                                                        <div class="course-name">{{ $entry->course->name }}</div>
                                                    @endif
                                                    <div class="lecturer">{{ $entry->lecturer->name ?? 'N/A' }}</div>
                                                    @if($entry->room)
                                                        <span class="room">{{ $entry->room->name }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            {{-- All courses export --}}
            @foreach($courses as $course)
            <div class="course-section">
                <div class="course-header">
                    <h2>{{ $course['name'] }}</h2>
                    <p>{{ $course['entries']->count() }} units scheduled</p>
                </div>

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
                        @php
                            $timeSlots = [
                                1 => '7:00 AM - 10:00 AM',
                                2 => '10:00 AM - 1:00 PM',
                                3 => '1:00 PM - 4:00 PM',
                                4 => '4:00 PM - 7:00 PM'
                            ];
                            $dayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
                        @endphp
                        
                        @foreach($timeSlots as $slotNum => $timeLabel)
                            <tr>
                                <td class="time-column">{{ $timeLabel }}</td>
                                @foreach($dayNames as $dayNum => $dayName)
                                    @php
                                        $courseEntries = $course['entries']->filter(function ($entry) use ($dayNum, $slotNum) {
                                            return (int)$entry->day_of_week === (int)$dayNum && (int)$entry->slot === (int)$slotNum;
                                        });
                                    @endphp
                                    <td>
                                        @foreach($courseEntries as $entry)
                                            <div class="entry">
                                                <div class="unit-code">{{ $entry->unit->code ?? 'N/A' }}</div>
                                                @if($entry->course)
                                                    <div class="course-name">{{ $entry->course->name }}</div>
                                                @endif
                                                <div class="lecturer">{{ $entry->lecturer->name ?? 'N/A' }}</div>
                                                @if($entry->room)
                                                    <span class="room">{{ $entry->room->name }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
        @endif

        <div class="summary">
            <h3>Summary</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <h4>Total Units</h4>
                    <div class="number">{{ $timetable->entries->pluck('unit')->unique('id')->count() }}</div>
                </div>
                <div class="summary-item">
                    <h4>Courses</h4>
                    <div class="number">{{ $courses->count() }}</div>
                </div>
                <div class="summary-item">
                    <h4>Lecturers</h4>
                    <div class="number">{{ $timetable->entries->pluck('lecturer')->unique('id')->count() }}</div>
                </div>
                <div class="summary-item">
                    <h4>Rooms</h4>
                    <div class="number">{{ $timetable->entries->pluck('room')->unique('id')->count() }}</div>
                </div>
            </div>
        </div>
    @endif
</body>
</html>
