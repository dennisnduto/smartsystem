<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $institution->name }} - Institutional Report</title>
    <style>
        @page {
            margin: 0.5cm;
            size: A4 portrait;
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            margin: 0;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 10px;
        }
        .header table {
            width: 100%;
            border: none;
        }
        .header td {
            border: none;
            vertical-align: middle;
        }
        .logo-txt {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
            letter-spacing: -1px;
        }
        .report-title {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .section-title {
            color: #1e293b; 
            font-size: 18px;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-left: 10px;
            border-left: 5px solid #4f46e5;
            font-weight: bold;
        }
        
        /* Stats Grid */
        .stats-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-table {
            width: 100%;
            border: none;
            margin-bottom: 20px;
        }
        .stats-card {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .stats-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stats-value {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }

        /* Detailed Tables */
        table.data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 25px;
        }
        table.data-table th, table.data-table td { 
            border: 1px solid #e2e8f0; 
            padding: 12px 10px; 
            text-align: left; 
            font-size: 11px;
        }
        table.data-table th { 
            background-color: #4f46e5; 
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f1f5f9;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        .badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-published { background-color: #dcfce7; color: #166534; }
        .badge-draft { background-color: #fef9c3; color: #854d0e; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td><div class="logo-txt">{{ strtoupper($institution->name) }}</div></td>
                <td><div class="report-title">Institution Management Report</div></td>
            </tr>
        </table>
        <div style="font-size: 10px; color: #64748b; margin-top: 5px;">
            Generated on: {{ $generated_at->format('F j, Y, g:i A') }}
        </div>
    </div>

    <div class="section-title">Executive Summary</div>
    <table class="stats-table">
        <tr>
            <td width="33%">
                <div class="stats-card">
                    <div class="stats-label">Departments</div>
                    <div class="stats-value">{{ $stats['total_departments'] }}</div>
                </div>
            </td>
            <td width="33%">
                <div class="stats-card">
                    <div class="stats-label">Active Courses</div>
                    <div class="stats-value">{{ $stats['total_courses'] }}</div>
                </div>
            </td>
            <td width="33%">
                <div class="stats-card">
                    <div class="stats-label">Teaching Staff</div>
                    <div class="stats-value">{{ $stats['total_lecturers'] }}</div>
                </div>
            </td>
        </tr>
        <tr>
            <td width="33%">
                <div class="stats-card" style="margin-top: 10px;">
                    <div class="stats-label">Enrolled Students</div>
                    <div class="stats-value">{{ $stats['total_students'] }}</div>
                </div>
            </td>
            <td width="33%">
                <div class="stats-card" style="margin-top: 10px;">
                    <div class="stats-label">Learning Spaces</div>
                    <div class="stats-value">{{ $stats['total_rooms'] }}</div>
                </div>
            </td>
            <td width="33%">
                <div class="stats-card" style="margin-top: 10px;">
                    <div class="stats-label">Active Timetables</div>
                    <div class="stats-value">{{ $stats['active_timetables'] }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Department Breakdown</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Department Name</th>
                <th style="text-align: center;">Courses</th>
                <th style="text-align: center;">Rooms</th>
                <th style="text-align: center;">Lecturers</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $dept)
                <tr>
                    <td style="font-weight: bold;">{{ $dept->name }}</td>
                    <td style="text-align: center;">{{ $dept->courses_count }}</td>
                    <td style="text-align: center;">{{ $dept->rooms_count }}</td>
                    <td style="text-align: center;">{{ $dept->lecturers_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Recent Timetables Activity</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Timetable Name</th>
                <th>Department</th>
                <th style="text-align: center;">Status</th>
                <th>Academic Focus</th>
            </tr>
        </thead>
        <tbody>
            @foreach($timetables as $timetable)
                <tr>
                    <td style="font-weight: bold;">{{ $timetable->name }}</td>
                    <td>{{ $timetable->department->name ?? 'Institution Wide' }}</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $timetable->status === 'published' ? 'badge-published' : 'badge-draft' }}">
                            {{ ucfirst($timetable->status) }}
                        </span>
                    </td>
                    <td>{{ $timetable->academic_year }} - {{ $timetable->semester }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Faculty Resources</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Lecturer Name</th>
                <th>Department</th>
                <th>Email Address</th>
                <th style="text-align: center;">Assignments</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lecturers->take(15) as $lecturer)
                <tr>
                    <td style="font-weight: bold;">{{ $lecturer->name }}</td>
                    <td>{{ $lecturer->department->name ?? 'N/A' }}</td>
                    <td>{{ $lecturer->email }}</td>
                    <td style="text-align: center;">{{ $lecturer->course_unit_years_count }}</td>
                </tr>
            @endforeach
            @if($lecturers->count() > 15)
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b; font-style: italic;">
                        Showing first 15 lecturers. Total count: {{ $lecturers->count() }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} SMART System • Internal Institution Report • Page 1
    </div>
</body>
</html>
