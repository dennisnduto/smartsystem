<?php

namespace Tests\Feature\Auth;

use App\Models\Course;
use App\Models\Department;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Storage::fake('public');
        $institution = Institution::create(['name' => 'Test University']);
        $department = Department::create([
            'institution_id' => $institution->id,
            'name' => 'Computer Science',
        ]);
        $course = Course::create([
            'department_id' => $department->id,
            'code' => 'BSC-CS',
            'name' => 'Computer Science',
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'institution_id' => $institution->id,
            'course_id' => $course->id,
            'year_of_study' => 'Y1',
            'school_id' => UploadedFile::fake()->create(
                'school-id.pdf',
                100,
                'application/pdf'
            ),
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'student',
            'institution_id' => $institution->id,
            'is_approved' => false,
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        Storage::disk('public')->assertExists($user->school_id_path);
    }
}
