<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>System Summary Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .stats-row {
            display: table-row;
        }
        .stats-cell {
            display: table-cell;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .stats-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .institution-item {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #eee;
        }
        .institution-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
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
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Smart University Timetable System</h1>
        <h2>Summary Report</h2>
        <p>Generated on {{ $generated_at->format('F d, Y \a\t g:i A') }}</p>
    </div>

    <!-- Overall Statistics -->
    <div class="section">
        <h2>System Overview</h2>
        <table>
            <tr>
                <th>Metric</th>
                <th>Count</th>
            </tr>
            <tr>
                <td>Total Institutions</td>
                <td>{{ $institutions->count() }}</td>
            </tr>
            <tr>
                <td>Total Users</td>
                <td>{{ array_sum($user_stats['by_role']->toArray()) }}</td>
            </tr>
            <tr>
                <td>Institution Admins</td>
                <td>{{ $user_stats['by_role']['institution_admin'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Lecturers</td>
                <td>{{ $user_stats['by_role']['lecturer'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Students</td>
                <td>{{ $user_stats['by_role']['student'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Published Timetables</td>
                <td>{{ $timetable_stats['total_published'] }}</td>
            </tr>
            <tr>
                <td>Draft Timetables</td>
                <td>{{ $timetable_stats['total_draft'] }}</td>
            </tr>
        </table>
    </div>

    <!-- Institutions Details -->
    <div class="section">
        <h2>Institution Details</h2>
        @foreach($institutions as $institution)
            <div class="institution-item">
                <div class="institution-name">{{ $institution->name }}</div>
                <div><strong>Total Users:</strong> {{ $institution->users_count }}</div>
                <div><strong>Departments:</strong> {{ $institution->departments_count }}</div>
                <div><strong>Created:</strong> {{ $institution->created_at->format('M d, Y') }}</div>
                
                @if(isset($user_stats['by_institution'][$institution->id]))
                    <div><strong>User Breakdown:</strong>
                        @foreach($user_stats['by_institution'][$institution->id] as $userStat)
                            {{ ucfirst(str_replace('_', ' ', $userStat->role)) }}: {{ $userStat->count }}
                            @if(!$loop->last), @endif
                        @endforeach
                    </div>
                @endif

                @if(isset($timetable_stats['by_institution'][$institution->id]))
                    <div><strong>Timetables:</strong>
                        @foreach($timetable_stats['by_institution'][$institution->id] as $timetableStat)
                            {{ ucfirst($timetableStat->status) }}: {{ $timetableStat->count }}
                            @if(!$loop->last), @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Timetable Statistics -->
    <div class="section">
        <h2>Timetable Activity</h2>
        <table>
            <thead>
                <tr>
                    <th>Institution</th>
                    <th>Published</th>
                    <th>Draft</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($institutions as $institution)
                    @php
                        $published = 0;
                        $draft = 0;
                        if(isset($timetable_stats['by_institution'][$institution->id])) {
                            foreach($timetable_stats['by_institution'][$institution->id] as $stat) {
                                if($stat->status === 'published') $published = $stat->count;
                                if($stat->status === 'draft') $draft = $stat->count;
                            }
                        }
                        $total = $published + $draft;
                    @endphp
                    <tr>
                        <td>{{ $institution->name }}</td>
                        <td>{{ $published }}</td>
                        <td>{{ $draft }}</td>
                        <td>{{ $total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- User Distribution -->
    <div class="section">
        <h2>User Distribution by Institution</h2>
        <table>
            <thead>
                <tr>
                    <th>Institution</th>
                    <th>Admins</th>
                    <th>Lecturers</th>
                    <th>Students</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($institutions as $institution)
                    @php
                        $admins = 0;
                        $lecturers = 0;
                        $students = 0;
                        if(isset($user_stats['by_institution'][$institution->id])) {
                            foreach($user_stats['by_institution'][$institution->id] as $stat) {
                                if($stat->role === 'institution_admin') $admins = $stat->count;
                                if($stat->role === 'lecturer') $lecturers = $stat->count;
                                if($stat->role === 'student') $students = $stat->count;
                            }
                        }
                        $total = $admins + $lecturers + $students;
                    @endphp
                    <tr>
                        <td>{{ $institution->name }}</td>
                        <td>{{ $admins }}</td>
                        <td>{{ $lecturers }}</td>
                        <td>{{ $students }}</td>
                        <td>{{ $total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This report was automatically generated by the Smart University Timetable System</p>
        <p>Report ID: {{ md5($generated_at->timestamp) }}</p>
    </div>
</body>
</html>