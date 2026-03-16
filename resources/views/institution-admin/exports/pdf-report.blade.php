<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Institution Report - {{ $institution->name }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #333; 
            padding-bottom: 20px; 
            margin-bottom: 30px;
        }
        .section { 
            margin-bottom: 30px; 
        }
        .section h2 { 
            color: #333; 
            border-bottom: 1px solid #ccc; 
            padding-bottom: 5px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left;
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px; 
            margin-bottom: 20px;
        }
        .stat-card { 
            border: 1px solid #ddd; 
            padding: 15px; 
            text-align: center; 
            background-color: #f9f9f9;
        }
        .stat-number { 
            font-size: 24px; 
            font-weight: bold; 
            color: #333;
        }
        .stat-label { 
            font-size: 12px; 
            color: #666; 
            text-transform: uppercase;
        }
        .footer { 
            margin-top: 40px; 
            padding-top: 20px; 
            border-top: 1px solid #ccc; 
            text-align: center; 
            font-size: 12px; 
            color: #666;
        }
        @media print {
            body { margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $institution->name }}</h1>
        <h2>Institution Report</h2>
        <p>Generated on: {{ $generated_at->format('F j, Y, g:i a') }}</p>
    </div>

    <div class="section">
        <h2>📊 Institution Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_departments'] }}</div>
                <div class="stat-label">Departments</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_courses'] }}</div>
                <div class="stat-label">Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_lecturers'] }}</div>
                <div class="stat-label">Lecturers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_students'] }}</div>
                <div class="stat-label">Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_rooms'] }}</div>
                <div class="stat-label">Rooms</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['active_timetables'] }}</div>
                <div class="stat-label">Active Timetables</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>🏢 Departments</h2>
        <table>
            <thead>
                <tr>
                    <th>Department Name</th>
                    <th>Courses</th>
                    <th>Rooms</th>
                    <th>Lecturers</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    <tr>
                        <td>{{ $dept->name }}</td>
                        <td>{{ $dept->courses_count }}</td>
                        <td>{{ $dept->rooms_count }}</td>
                        <td>{{ $dept->lecturers_count ?? 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No departments found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>🏠 Rooms</h2>
        <table>
            <thead>
                <tr>
                    <th>Room Name</th>
                    <th>Department</th>
                    <th>Capacity</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                    <tr>
                        <td>{{ $room->name }}</td>
                        <td>{{ $room->department->name ?? 'N/A' }}</td>
                        <td>{{ $room->capacity ?? 'N/A' }}</td>
                        <td>{{ $room->room_type ?? 'Standard' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No rooms found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>👨‍🏫 Lecturers</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Total Classes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lecturers as $lecturer)
                    <tr>
                        <td>{{ $lecturer->name }}</td>
                        <td>{{ $lecturer->email }}</td>
                        <td>{{ $lecturer->department->name ?? 'N/A' }}</td>
                        <td>{{ $lecturer->course_unit_years_count ?? 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No lecturers found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>📅 Recent Timetables</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($timetables as $timetable)
                    <tr>
                        <td>{{ $timetable->name }}</td>
                        <td>
                            <span style="background-color: {{ $timetable->status === 'published' ? '#d4edda' : '#fff3cd' }}; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                                {{ ucfirst($timetable->status) }}
                            </span>
                        </td>
                        <td>{{ $timetable->academic_year ?? 'N/A' }}</td>
                        <td>{{ $timetable->semester ?? 'N/A' }}</td>
                        <td>{{ $timetable->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No timetables found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This report was generated automatically by Smart University Timetable System</p>
        <p>Report ID: {{ $institution->id }}-{{ $generated_at->format('YmdHis') }}</p>
    </div>

    <div class="no-print">
        <p style="text-align: center; margin-top: 30px;">
            <strong>Print this page to save as PDF or use your browser's "Save as PDF" function</strong>
        </p>
    </div>
</body>
</html>
