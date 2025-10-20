@extends('layouts.app')

@section('content')
<header class="bg-gradient-to-r from-indigo-600 to-purple-700 shadow-lg">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-white">Institution Reports</h1>
                <p class="text-indigo-100 mt-1">{{ $institution->name }} - Detailed Reports</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('institution-admin.dashboard') }}" class="bg-white text-indigo-600 hover:bg-indigo-50 font-semibold py-2 px-4 rounded-lg shadow transition">
                    ← Back to Dashboard
                </a>
                <a href="{{ route('institution-admin.analytics') }}" class="bg-indigo-500 hover:bg-indigo-400 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                    📊 View Analytics
                </a>
            </div>
        </div>
    </div>
</header>

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-blue-500">
                <div class="text-center">
                    <div class="text-3xl mb-2">🏢</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Departments</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $reports_data['summary_stats']['total_departments'] }}</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-green-500">
                <div class="text-center">
                    <div class="text-3xl mb-2">👨‍🏫</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Lecturers</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $reports_data['summary_stats']['total_lecturers'] }}</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-yellow-500">
                <div class="text-center">
                    <div class="text-3xl mb-2">🎓</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Students</h3>
                    <p class="text-3xl font-bold text-yellow-600">{{ $reports_data['summary_stats']['total_students'] }}</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-purple-500">
                <div class="text-center">
                    <div class="text-3xl mb-2">📚</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Courses</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ $reports_data['summary_stats']['total_courses'] }}</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border-l-4 border-red-500">
                <div class="text-center">
                    <div class="text-3xl mb-2">🏠</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Rooms</h3>
                    <p class="text-3xl font-bold text-red-600">{{ $reports_data['summary_stats']['total_rooms'] }}</p>
                </div>
            </div>
        </div>

        <!-- Institution Overview -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    🎯 Institution Overview - {{ $institution->name }}
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-blue-800 mb-2">Academic Structure</h4>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• {{ $reports_data['summary_stats']['total_departments'] }} Active Departments</li>
                            <li>• {{ $reports_data['summary_stats']['total_courses'] }} Total Courses</li>
                            <li>• {{ $reports_data['summary_stats']['total_rooms'] }} Available Rooms</li>
                        </ul>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-green-800 mb-2">Human Resources</h4>
                        <ul class="text-sm text-green-700 space-y-1">
                            <li>• {{ $reports_data['summary_stats']['total_lecturers'] }} Teaching Staff</li>
                            <li>• {{ $reports_data['summary_stats']['total_students'] }} Enrolled Students</li>
                            <li>• {{ $reports_data['summary_stats']['total_lecturers'] > 0 ? round($reports_data['summary_stats']['total_students'] / $reports_data['summary_stats']['total_lecturers'], 1) : 0 }}:1 Student-Lecturer Ratio</li>
                        </ul>
                    </div>
                    
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg">
                        <h4 class="font-semibold text-purple-800 mb-2">Operational Status</h4>
                        <ul class="text-sm text-purple-700 space-y-1">
                            <li>• {{ count($reports_data['recent_timetables']) }} Recent Timetables</li>
                            <li>• {{ $reports_data['recent_timetables']->where('status', 'published')->count() }} Published</li>
                            <li>• {{ $reports_data['recent_timetables']->where('status', 'draft')->count() }} In Development</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Institution Creation Date -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Institution established:</span> {{ $institution->created_at->format('F j, Y') }}
                        ({{ $institution->created_at->diffForHumans() }})
                    </p>
                </div>
            </div>
        </div>

        <!-- Departments Breakdown -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">🏢 Departments Breakdown</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Courses</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rooms</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Resources</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($reports_data['departments'] as $department)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                            <div class="text-sm font-medium text-gray-900">{{ $department->name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $department->courses_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $department->rooms_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="font-medium">{{ ($department->courses_count ?? 0) + ($department->rooms_count ?? 0) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $department->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <div class="text-4xl mb-4">🏢</div>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No departments found</p>
                                        <p class="text-gray-500">Create departments to see detailed breakdowns here.</p>
                                        <a href="{{ route('institution-admin.departments.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                                            Create Department
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Timetables Activity -->
        <div class="bg-white overflow-hidden shadow-lg rounded-xl mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">📅 Recent Timetables Activity</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timetable Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($reports_data['recent_timetables'] as $timetable)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $timetable->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $timetable->department->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $timetable->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($timetable->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $timetable->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $timetable->updated_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <div class="text-4xl mb-4">📅</div>
                                        <p class="text-lg font-medium text-gray-900 mb-2">No timetables created yet</p>
                                        <p class="text-gray-500">Generate your first timetable to see activity here.</p>
                                        <a href="{{ route('institution-admin.timetables.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600 transition">
                                            Create Timetable
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📄 Export Institution Report</h3>
            <p class="text-gray-600 mb-6">Generate comprehensive reports for {{ $institution->name }} in various formats.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button onclick="exportInstitutionReport('pdf')" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-6 rounded-lg shadow transition flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Export as PDF
                </button>
                
                <button onclick="exportInstitutionReport('csv')" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg shadow transition flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export as CSV
                </button>
                
                <button onclick="exportInstitutionReport('excel')" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg shadow transition flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Export as Excel
                </button>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="bg-white p-6 rounded-xl shadow-lg text-center">
            <p class="text-gray-600">
                Report generated on {{ now()->format('F j, Y \a\t g:i A') }} for {{ $institution->name }}
            </p>
            <p class="text-sm text-gray-500 mt-2">
                This report contains institution-specific data and is confidential.
            </p>
        </div>
    </div>
</div>

<script>
function exportInstitutionReport(format) {
    showNotification(`Generating ${format.toUpperCase()} report for {{ $institution->name }}...`, 'info');
    
    // Simulate export process
    setTimeout(() => {
        showNotification(`${format.toUpperCase()} report generated successfully!`, 'success');
        
        // Here you would typically trigger the actual download
        // window.open(`/institution-admin/reports/export/${format}`, '_blank');
    }, 2000);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="${type === 'success' ? 'M5 13l4 4L19 7' : type === 'error' ? 'M6 18L18 6M6 6l12 12' : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}">
                </path>
            </svg>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 100);
    
    // Remove after delay
    setTimeout(() => {
        notification.style.transform = 'translateX(full)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 4000);
}
</script>
@endsection