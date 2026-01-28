<x-guest-layout>
    <div class="mb-4">
        <h2 class="text-2xl font-bold text-center">Student Registration</h2>
        <p class="text-sm text-gray-600 text-center mt-1">Register as a student to access your timetable</p>
    </div>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Institution Selection -->
        <div class="mt-4">
            <x-input-label for="institution_id" :value="__('Institution')" />
            <select id="institution_id" name="institution_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Select Institution</option>
                @foreach(\App\Models\Institution::orderBy('name')->get() as $institution)
                    <option value="{{ $institution->id }}" {{ old('institution_id') == $institution->id ? 'selected' : '' }}>{{ $institution->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('institution_id')" class="mt-2" />
        </div>

        <!-- Course Selection -->
        <div class="mt-4">
            <x-input-label for="course_id" :value="__('Course')" />
            <select id="course_id" name="course_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Select Institution first</option>
            </select>
            <x-input-error :messages="$errors->get('course_id')" class="mt-2" />
            <p class="mt-1 text-sm text-gray-600">Select your course to view your timetable</p>
        </div>

        <!-- School ID Upload -->
        <div class="mt-4">
            <x-input-label for="school_id" :value="__('School ID (Upload)')" />
            <input id="school_id" name="school_id" type="file" accept="image/*,.pdf" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
            <x-input-error :messages="$errors->get('school_id')" class="mt-2" />
            <p class="mt-1 text-sm text-gray-600">Upload a clear image or PDF of your school ID for admin verification</p>
        </div>

        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
            <p class="text-sm text-blue-800">
                <strong>Note:</strong> Your account will require admin approval before you can access the system. You will be notified once your account is approved.
            </p>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const institutionSelect = document.getElementById('institution_id');
            const courseSelect = document.getElementById('course_id');
            const oldInstitutionId = @json(old('institution_id'));
            const oldCourseId = @json(old('course_id'));

            function loadCourses(institutionId) {
                courseSelect.innerHTML = '<option value="">Loading courses...</option>';
                courseSelect.disabled = true;

                if (!institutionId) {
                    courseSelect.innerHTML = '<option value="">Select Institution first</option>';
                    courseSelect.disabled = false;
                    return;
                }

                fetch(`/api/courses?institution_id=${institutionId}`)
                    .then(response => response.json())
                    .then(data => {
                        courseSelect.innerHTML = '<option value="">Select Course</option>';
                        data.forEach(course => {
                            const option = document.createElement('option');
                            option.value = course.id;
                            option.textContent = course.name + (course.code ? ` (${course.code})` : '');
                            if (oldCourseId && course.id == oldCourseId) {
                                option.selected = true;
                            }
                            courseSelect.appendChild(option);
                        });
                        courseSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error loading courses:', error);
                        courseSelect.innerHTML = '<option value="">Error loading courses</option>';
                        courseSelect.disabled = false;
                    });
            }

            institutionSelect.addEventListener('change', function() {
                loadCourses(this.value);
            });

            // Load courses if institution is pre-selected (e.g., from validation errors)
            if (oldInstitutionId) {
                loadCourses(oldInstitutionId);
            }
        });
    </script>
</x-guest-layout>
