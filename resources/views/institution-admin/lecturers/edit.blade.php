@extends('layouts.app')

@section('content')
<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Lecturer: ') }} {{ $lecturer->name }}
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
                <form method="POST" action="{{ route('institution-admin.lecturers.update', $lecturer) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $lecturer->name) }}" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $lecturer->email) }}" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Employee ID -->
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID</label>
                            <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id', $lecturer->employee_id) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $lecturer->phone) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700">Department *</label>
                            <select name="department_id" id="department_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" 
                                        {{ old('department_id', $lecturer->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" id="password"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Leave blank to keep current password</p>
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
                            <!-- Dynamic course assignment fields will be populated here -->
                            @if(empty($lecturerCourseAssignments))
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end p-4 bg-gray-50 rounded-lg mb-4">
                                    <div class="md:col-span-4">
                                        <label class="block text-sm font-medium text-gray-700">Course</label>
                                        <select name="course_assignments[0][course_id]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">Select Course</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}">{{ $course->name }} ({{ optional($course->department)->name }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Academic Year</label>
                                        <select name="course_assignments[0][academic_year]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">Select Year</option>
                                            <option value="Y1">Year 1</option>
                                            <option value="Y2">Year 2</option>
                                            <option value="Y3">Year 3</option>
                                            <option value="Y4">Year 4</option>
                                            <option value="Y5">Year 5</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Semester</label>
                                        <select name="course_assignments[0][semester]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">Select Semester</option>
                                            <option value="S1">Semester 1</option>
                                            <option value="S2">Semester 2</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-sm font-medium text-gray-700">Unit</label>
                                        <select name="course_assignments[0][unit_id]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">Select Unit</option>
                                            @foreach($allUnits as $u)
                                                <option value="{{ $u->id }}">{{ $u->code }} — {{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-1"></div>
                                </div>
                            @endif
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
                            Update Lecturer
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
    
    // Existing course assignments
    const existingAssignments = @json($lecturerCourseAssignments ?? []);

    // Data sources for dynamic unit select
    const cuy = @json($cuyForJs ?? []);
    const allUnits = @json($unitsForJs ?? []);
    
    // Academic year options
    const academicYears = {
        'Y1': 'Year 1',
        'Y2': 'Year 2', 
        'Y3': 'Year 3',
        'Y4': 'Year 4',
        'Y5': 'Year 5'
    };
    
    function createAssignmentRow(index = assignmentIndex, existingData = null) {
        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 md:grid-cols-12 gap-4 items-end p-4 bg-gray-50 rounded-lg mb-4';
        row.dataset.index = index;
        
        let coursesOptions = '<option value="">Select Course</option>';
        courses.forEach(course => {
            const selected = existingData && existingData.course_id == course.id ? 'selected' : '';
            coursesOptions += `<option value="${course.id}" data-lab-required="${course.lab_required}" ${selected}>${course.name} (${course.department})</option>`;
        });
        
        let yearOptions = '';
        Object.keys(academicYears).forEach(function(key){
            const label = academicYears[key];
            const selected = existingData && existingData.academic_year === key ? 'selected' : '';
            yearOptions += `<option value="${key}" ${selected}>${label}</option>`;
        });
        
        const isLabOnlyChecked = existingData && existingData.is_lab_only ? 'checked' : '';
        const notesValue = existingData ? (existingData.notes || '') : '';
        
        // Initialize unit options - will be populated by refreshUnitOptions after row is added
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
                <select name="course_assignments[${index}][semester]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 semester-select">
                    <option value="">Select Semester (Optional)</option>
                    <option value="S1" ${existingData && existingData.semester==='S1' ? 'selected' : ''}>Semester 1</option>
                    <option value="S2" ${existingData && existingData.semester==='S2' ? 'selected' : ''}>Semester 2</option>
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
                    <input type="checkbox" name="course_assignments[${index}][is_lab_only]" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded lab-only-checkbox" ${isLabOnlyChecked}>
                </div>
                <div class="ml-3 text-sm">
                    <label class="font-medium text-gray-700">Lab Only</label>
                </div>
            </div>
            
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <input type="text" name="course_assignments[${index}][notes]" value="${notesValue}" placeholder="Optional notes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            
            <div class="md:col-span-1">
                <button type="button" class="remove-assignment bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded" title="Remove Assignment">
                    ×
                </button>
            </div>
        `;
        
        return row;
    }
    
    function addEventListenersToRow(row, existingData = null) {
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
            
            if (courseId) {
                // Get suggested units from course-unit-year mappings (for reference)
                let suggestedUnits = cuy.filter(m => String(m.course_id) === String(courseId));
                if (year) {
                    suggestedUnits = suggestedUnits.filter(u => u.academic_year === year);
                }
                if (semester) {
                    suggestedUnits = suggestedUnits.filter(u => u.semester === semester);
                }
                const suggestedUnitIds = new Set(suggestedUnits.map(u => String(u.unit_id)));
                
                // Show ALL units from the institution, optionally filtered by year level
                let availableUnits = allUnits;
                if (year) {
                    const yearNum = /^Y(\d+)$/.test(year) ? parseInt(year.slice(1), 10) : null;
                    if (yearNum) {
                        availableUnits = availableUnits.filter(u => u.year_level === yearNum);
                    }
                }
                
                // Add all available units to options
                availableUnits.forEach(u => {
                    const isSuggested = suggestedUnitIds.has(String(u.id));
                    const label = `${u.code} — ${u.name}${isSuggested ? ' (suggested)' : ''}`;
                    options += `<option value="${u.id}">${label}</option>`;
                });
            }
            unitSelect.innerHTML = options;
            if (existingData && existingData.unit_id) {
                unitSelect.value = String(existingData.unit_id);
            }
        }
        
        courseSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const isLabRequired = selectedOption.dataset.labRequired === 'true';
            labCheckbox.checked = !!isLabRequired;
            labCheckbox.disabled = !!isLabRequired;
            refreshUnitOptions();
        });
        yearSelect.addEventListener('change', refreshUnitOptions);
        semesterSelect.addEventListener('change', refreshUnitOptions);
        refreshUnitOptions();
        
        // Remove assignment
        row.querySelector('.remove-assignment').addEventListener('click', function() {
            row.remove();
        });
    }
    
    // Add course assignment
    addCourseBtn.addEventListener('click', function() {
        const row = createAssignmentRow();
        container.appendChild(row);
        assignmentIndex++;
        addEventListenersToRow(row);
    });
    
    // Load existing assignments
    if (existingAssignments.length > 0) {
        existingAssignments.forEach((assignment, index) => {
            const row = createAssignmentRow(index, assignment);
            container.appendChild(row);
            addEventListenersToRow(row, assignment);
            assignmentIndex = index + 1;
        });
    } else if (courses.length > 0) {
        // Add empty assignment if no existing assignments but courses are available
        addCourseBtn.click();
    }
    
    // Handle form submission validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const assignments = container.children;
        
        if (assignments.length === 0) {
            e.preventDefault();
            alert('Please add at least one course assignment.');
            return false;
        }
        
        // Check for duplicate course assignments
        const courseIds = [];
        for (let i = 0; i < assignments.length; i++) {
            const courseSelect = assignments[i].querySelector('.course-select');
            const courseId = courseSelect.value;
            const yearSelect = assignments[i].querySelector('select[name*="academic_year"]');
            const year = yearSelect.value;
            
            if (!courseId || !year) {
                e.preventDefault();
                alert('Please fill all required fields for course assignments.');
                return false;
            }
            
            const key = courseId + '_' + year;
            if (courseIds.includes(key)) {
                e.preventDefault();
                alert('Duplicate course and year combination found. Please remove duplicates.');
                return false;
            }
            courseIds.push(key);
        }
    });
});
</script>
@endsection
