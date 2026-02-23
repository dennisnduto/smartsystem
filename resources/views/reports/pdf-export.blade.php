<!DOCTYPE html>
<html>
<head>
    <title>System Report - {{ date('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        .section {
            margin: 30px 0;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-row {
            display: table-row;
        }
        .stats-cell {
            display: table-cell;
            padding: 10px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .stats-label {
            font-weight: bold;
            color: #374151;
        }
        .stats-value {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status-published {
            color: #059669;
            font-weight: bold;
        }
        .status-draft {
            color: #d97706;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Smart University Timetable System Report</h1>
        <p>Generated on {{ date('F j, Y \a\t g:i A') }}</p>
    </div>

    <!-- System Statistics -->
    <div class="section">
        <div class="section-title">System Statistics</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell stats-label">Total Institutions:</div>
                <div class="stats-cell stats-value">{{ $stats['total_institutions'] }}</div>
                <div class="stats-cell stats-label">Institution Admins:</div>
                <div class="stats-cell stats-value">{{ $stats['total_admins'] }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-cell stats-label">Total Users:</div>
                <div class="stats-cell stats-value">{{ $stats['total_users'] }}</div>
                <div class="stats-cell stats-label">Published Timetables:</div>
                <div class="stats-cell stats-value">{{ $stats['published_timetables'] }}</div>
            </div>
        </div>
    </div>

    <!-- Institutions -->
    <div class="section">
        <div class="section-title">Institution Details</div>
        @if(count($institutions) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Institution Name</th>
                        <th>Users</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($institutions as $institution)
                        <tr>
                            <td>{{ $institution->name }}</td>
                            <td>{{ $institution->users_count ?? 0 }}</td>
                            <td>{{ $institution->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No institutions found.</p>
        @endif
    </div>

    <!-- Recent Activities -->
    <div class="section">
        <div class="section-title">Recent Timetable Activities</div>
        @if(count($recent_activities) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Timetable</th>
                        <th>Institution</th>
                        <th>Status</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_activities as $activity)
                        <tr>
                            <td>{{ $activity->name ?? 'Unnamed' }}</td>
                            <td>{{ $activity->institution->name ?? 'N/A' }}</td>
                            <td>
                                <span class="status-{{ $activity->status ?? 'draft' }}">
                                    {{ ucfirst($activity->status ?? 'draft') }}
                                </span>
                            </td>
                            <td>{{ $activity->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No recent activities found.</p>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated by the Smart University Timetable System</p>
        <p>© {{ date('Y') }} Smart University Timetable. All rights reserved.</p>
    </div>
</body>
</html>