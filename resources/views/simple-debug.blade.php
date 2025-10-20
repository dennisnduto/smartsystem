<!DOCTYPE html>
<html>
<head>
    <title>Debug Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-6">Super Admin Dashboard DEBUG</h1>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            @if(isset($stats))
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900">Total Institutions</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['total_institutions'] ?? 'N/A' }}</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900">Total Admins</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['total_admins'] ?? 'N/A' }}</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900">Total Users</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['total_users'] ?? 'N/A' }}</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900">Published Timetables</h3>
                    <p class="text-3xl font-bold text-yellow-600">{{ $stats['total_timetables'] ?? 'N/A' }}</p>
                </div>
            @else
                <div class="bg-red-100 p-4 rounded">
                    <p class="text-red-700">No stats data found</p>
                </div>
            @endif
        </div>
        
        <!-- Recent Institutions -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h3 class="text-lg font-medium mb-4">Recent Institutions</h3>
            @if(isset($recent_institutions) && count($recent_institutions) > 0)
                @foreach($recent_institutions as $institution)
                    <div class="border-b pb-2 mb-2">
                        <p class="font-medium">{{ $institution->name }}</p>
                        <p class="text-sm text-gray-600">
                            Users: {{ $institution->users_count ?? 0 }} | 
                            Departments: {{ $institution->departments_count ?? 0 }}
                        </p>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500">No recent institutions found</p>
            @endif
        </div>
        
        <!-- Recent Admins -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h3 class="text-lg font-medium mb-4">Recent Admins</h3>
            @if(isset($recent_admins) && count($recent_admins) > 0)
                @foreach($recent_admins as $admin)
                    <div class="border-b pb-2 mb-2">
                        <p class="font-medium">{{ $admin->name }}</p>
                        <p class="text-sm text-gray-600">{{ $admin->institution->name ?? 'No Institution' }}</p>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500">No recent admins found</p>
            @endif
        </div>
        
        <div class="bg-blue-100 p-4 rounded">
            <h3 class="font-bold">Debug Info:</h3>
            <p>Stats: {{ isset($stats) ? 'YES' : 'NO' }}</p>
            <p>Recent Institutions: {{ isset($recent_institutions) ? count($recent_institutions) : 'NO' }}</p>
            <p>Recent Admins: {{ isset($recent_admins) ? count($recent_admins) : 'NO' }}</p>
        </div>
    </div>
</body>
</html>