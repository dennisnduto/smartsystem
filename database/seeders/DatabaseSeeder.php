<?php

namespace Database\Seeders;

use App\Models\{User, Institution, Department, Course, Unit, Room, Lecturer, TeachingGroup};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $inst = Institution::create(['name' => 'Smart Uni']);
        $dept = Department::create(['institution_id' => $inst->id, 'name' => 'Computer Science']);

        $cs101 = Unit::create(['code' => 'CS101', 'name' => 'Intro to CS']);
        $cs201 = Unit::create(['code' => 'CS201', 'name' => 'Data Structures']);

        $course = Course::create(['department_id' => $dept->id, 'code' => 'BSC-CS', 'name' => 'BSc Computer Science', 'year' => 1]);
        $course->units()->sync([$cs101->id, $cs201->id]);

        $groupA = TeachingGroup::create(['course_id' => $course->id, 'name' => 'Year1-A', 'size' => 40]);

        $room1 = Room::create(['name' => 'R-101', 'capacity' => 60, 'institution_id' => $inst->id, 'department_id' => $dept->id]);
        $room2 = Room::create(['name' => 'R-102', 'capacity' => 40, 'institution_id' => $inst->id, 'department_id' => $dept->id]);

        $lec = Lecturer::create(['name' => 'Dr. Ada Lovelace']);

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin', 'institution_id' => $inst->id,
        ]);

        User::factory()->create([
            'name' => 'Institution Admin',
            'email' => 'instadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'institution_admin', 'institution_id' => $inst->id,
        ]);

        User::factory()->create([
            'name' => 'Lecturer',
            'email' => 'lecturer@example.com',
            'password' => Hash::make('password'),
            'role' => 'lecturer', 'institution_id' => $inst->id,
            'lecturer_id' => $lec->id,
        ]);

        User::factory()->create([
            'name' => 'Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student', 'institution_id' => $inst->id,
        ]);
        $this->call(TimetableSeed::class);
    }
}
