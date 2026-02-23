@extends('layouts.app')

@section('content')
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('System Reports') }}
            </h2>
            <button onclick="generateReport()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Refresh Report
            </button>
        </div>
    </div>
</header>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Total Institutions</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['total_institutions'] ?? 0 }}</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Institution Admins</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['total_admins'] ?? 0 }}</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Total Users</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['total_users'] ?? 0 }}</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Total Timetables</h3>
                    <p class="text-3xl font-bold text-yellow-600">{{ $stats['total_timetables'] ?? 0 }}</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Published</h3>
                    <p class="text-3xl font-bold text-indigo-600">{{ $stats['published_timetables'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <!-- Institution Details -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-medium mb-4">Institution Details</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institution</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($institutions ?? [] as $institution)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $institution->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $institution->users_count ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $institution->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">No institutions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-medium mb-4">Recent Timetable Activities</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timetable</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institution</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recent_activities ?? [] as $activity)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $activity->name ?? 'Unnamed' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $activity->institution->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $activity->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($activity->status ?? 'draft') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $activity->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No recent activities found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Export Options -->
        <div class="mt-8 bg-gray-50 p-6 rounded-lg">
            <h3 class="text-lg font-medium mb-4">Export Options</h3>
            <div class="flex space-x-4">
                <a href="{{ route('super-admin.export-report', 'pdf') }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded inline-block text-center">
                    📄 Export as PDF
                </a>
                <a href="{{ route('super-admin.export-report', 'csv') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-block text-center">
                    📊 Export as CSV
                </a>
                <a href="{{ route('super-admin.export-report', 'excel') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-block text-center">
                    📈 Export as Excel
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function generateReport() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '🔄 Generating...';
    button.disabled = true;
    
    // Simulate report generation
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        
        // Show success message
        showNotification('Report refreshed successfully!', 'success');
        
        // Reload the page to get fresh data
        location.reload();
    }, 2000);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        document.body.removeChild(notification);
    }, 3000);
}
</script>
@endsection