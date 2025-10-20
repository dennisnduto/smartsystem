@extends('layouts.app')

@section('content')
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Lecturer') }}
            </h2>
            <a href="{{ route('institution-admin.lecturers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Lecturers
            </a>
        </div>
    </div>
</header>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <strong>Please fix the following errors:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <form method="POST" action="{{ route('institution-admin.lecturers.store') }}">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Employee ID -->
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID</label>
                            <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700">Department *</label>
                            <select name="department_id" id="department_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                            <input type="password" name="password" id="password" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                        </div>
                    </div>

                    <!-- Course Assignments -->
                    <div class="mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Course Assignments</h3>
                            <button type="button" id="add-course-assignment" class="bg-green-500 hover:bg-green-700 text-white text-sm font-bold py-1 px-3 rounded">
                                + Add Course
                            </button>
                        </div>
                        
                        <div id="course-assignments-container">
                            <!-- Dynamic course assignment fields will be added here -->
                        </div>
                        
                        @if($courses->isEmpty())
                            <div class="text-gray-500 text-center py-4 border-2 border-dashed border-gray-300 rounded-lg">
                                <p>No courses available. Please create courses first.</p>
                                <a href="{{ route('institution-admin.courses.index') }}" class="text-blue-600 hover:text-blue-800">
                                    Go to Course Management
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex items-center justify-end space-x-3">
                        <a href="{{ route('institution-admin.lecturers.index') }}" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Lecturer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addCourseBtn = document.getElementById('add-course-assignment');
    const container = document.getElementById('course-assignments-container');
    let assignmentIndex = 0;
    
    // Available courses data
    const courses = @json($coursesForJs);
    const cuy = @json($cuyForJs);
    const allUnits = @json($unitsForJs);
    
    // Academic year options
    const academicYears = {
        'Y1': 'Year 1',
        'Y2': 'Year 2', 
        'Y3': 'Year 3',
        'Y4': 'Year 4',
        'Y5': 'Year 5'
    };
    
    function createAssignmentRow(index = assignmentIndex) {
        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 md:grid-cols-12 gap-4 items-end p-4 bg-gray-50 rounded-lg mb-4';
        row.dataset.index = index;
        
        let coursesOptions = '<option value="">Select Course</option>';
        courses.forEach(course => {
            coursesOptions += `<option value="${course.id}" data-lab-required="${course.lab_required}">${course.name} (${course.department})</option>`;
        });
        
        let yearOptions = '';
        Object.keys(academicYears).forEach(function(key){
            const label = academicYears[key];
            yearOptions += `<option value="${key}">${label}</option>`;
        });

        // Unit options placeholder (will be populated based on course+year+semester)
        let unitOptions = '<option value="">Select Unit</option>';
        
        row.innerHTML = `
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700">Course</label>
                <select name="course_assignments[${index}][course_id]" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 course-select">
                    ${coursesOptions}
                </select>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Academic Year</label>
                <select name="course_assignments[${index}][academic_year]" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 year-select">
                    ${yearOptions}
                </select>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Semester</label>
                <select name="course_assignments[${index}][semester]" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 semester-select">
                    <option value="">Select Semester</option>
                    <option value="S1">Semester 1</option>
                    <option value="S2">Semester 2</option>
                </select>
            </div>
            
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700">Unit</label>
                <select name="course_assignments[${index}][unit_id]" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 unit-select">
                    ${unitOptions}
                </select>
            </div>
            
            <div class="md:col-span-2 flex items-center">
                <div class="flex items-center h-5">
                    <input type="checkbox" name="course_assignments[${index}][is_lab_only]" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded lab-only-checkbox">
                </div>
                <div class="ml-3 text-sm">
                    <label class="font-medium text-gray-700">Lab Only</label>
                </div>
            </div>
            
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <input type="text" name="course_assignments[${index}][notes]" placeholder="Optional notes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            
            <div class="md:col-span-1">
                <button type="button" class="remove-assignment bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded" title="Remove Assignment">
                    ×
                </button>
            </div>
        `;
        
        return row;
    }
    
    // Add course assignment
    addCourseBtn.addEventListener('click', function() {
        const row = createAssignmentRow();
        container.appendChild(row);
        assignmentIndex++;
        
        // Add event listeners for the new row
        const courseSelect = row.querySelector('.course-select');
        const yearSelect = row.querySelector('.year-select');
        const semesterSelect = row.querySelector('.semester-select');
        const unitSelect = row.querySelector('.unit-select');
        const labCheckbox = row.querySelector('.lab-only-checkbox');
        
        function refreshUnitOptions() {
            const courseId = courseSelect.value;
            const year = yearSelect.value;
            const semester = semesterSelect.value;
            let options = '<option value="">Select Unit</option>';
            if (courseId && year) {
                let units = cuy.filter(m => String(m.course_id) === String(courseId) && m.academic_year === year);
                if (semester) {
                    units = units.filter(u => u.semester === semester);
                }
                if (units.length === 0) {
                    // Fallback: filter all units by year level if possible
                    const yearNum = /^Y(\d+)$/.test(year) ? parseInt(year.slice(1), 10) : null;
                    let alt = allUnits;
                    if (yearNum) {
                        alt = alt.filter(u => u.year_level === yearNum);
                    }
                    alt.forEach(u => {
                        options += `<option value="${u.id}">${u.code} — ${u.name}</option>`;
                    });
                } else {
                    units.forEach(u => {
                        options += `<option value="${u.unit_id}">${u.unit_code} — ${u.unit_name}</option>`;
                    });
                }
            }
            unitSelect.innerHTML = options;
        }
        
        // Autoflag lab-only if the course requires labs
        courseSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const isLabRequired = selectedOption.dataset.labRequired === 'true';
            if (isLabRequired) {
                labCheckbox.checked = true;
                labCheckbox.disabled = true;
            } else {
                labCheckbox.disabled = false;
            }
            refreshUnitOptions();
        });
        yearSelect.addEventListener('change', refreshUnitOptions);
        semesterSelect.addEventListener('change', refreshUnitOptions);
        refreshUnitOptions();
        
        // Remove assignment
        row.querySelector('.remove-assignment').addEventListener('click', function() {
            row.remove();
        });
    });
    
    // Add initial assignment if courses are available
    if (courses.length > 0) {
        addCourseBtn.click();
    }
    
});
</script>
@endsection
