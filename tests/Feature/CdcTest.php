<?php

use App\Models\Department;
use App\Models\User;

function createCdcUser(): User
{
    return User::create([
        'name' => 'CDC Admin',
        'email' => 'cdc@gmail.com',
        'password' => bcrypt('password'),
        'role' => 'cdc',
    ]);
}

function createHodUserForTest(array $overrides = []): User
{
    return User::create(array_merge([
        'name' => 'HOD User',
        'email' => 'hod.test@example.com',
        'password' => bcrypt('password123'),
        'role' => 'hod',
    ], $overrides));
}

function createHodUser(array $overrides = []): User
{
    return User::create(array_merge([
        'name' => 'HOD User',
        'email' => 'hod@example.com',
        'password' => bcrypt('password123'),
        'role' => 'hod',
    ], $overrides));
}

function createFacultyUser(array $overrides = []): User
{
    return User::create(array_merge([
        'name' => 'Faculty User',
        'email' => 'faculty@example.com',
        'password' => bcrypt('password123'),
        'role' => 'faculty',
    ], $overrides));
}

function createModeratorUser(array $overrides = []): User
{
    return User::create(array_merge([
        'name' => 'Moderator User',
        'email' => 'moderator@example.com',
        'password' => bcrypt('password123'),
        'role' => 'moderator',
    ], $overrides));
}

function createRegularUser(): User
{
    return User::create([
        'name' => 'Regular User',
        'email' => 'user@gmail.com',
        'password' => bcrypt('password'),
        'role' => null,
    ]);
}

function validBasketData(): array
{
    return [
        ['basket_name' => 'Core', 'courses' => 2, 'cl' => 3, 'tl' => 1, 'll' => 2, 'credits' => 4, 'marks' => 100],
        ['basket_name' => 'Elective', 'courses' => 1, 'cl' => 2, 'tl' => 0, 'll' => 2, 'credits' => 3, 'marks' => 75],
    ];
}

function validProgrammeData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Computer Science',
        'code' => 'CS',
        'year' => '2026',
        'award_class_subjects' => 0,
        'baskets' => validBasketData(),
    ], $overrides);
}

function createProgramme(array $overrides = []): Department
{
    $department = Department::create(array_merge([
        'name' => 'Computer Science',
        'code' => 'CS',
        'year' => '2026',
        'award_class_subjects' => 0,
    ], $overrides));

    foreach (validBasketData() as $basket) {
        $department->courseBaskets()->create([
            'basket_name' => $basket['basket_name'],
            'courses' => $basket['courses'],
            'cl' => $basket['cl'],
            'tl' => $basket['tl'],
            'll' => $basket['ll'],
            'hours' => $basket['cl'] + $basket['tl'] + $basket['ll'],
            'credits' => $basket['credits'],
            'marks' => $basket['marks'],
        ]);
    }

    return $department->fresh('courseBaskets');
}

function validCourseRows(Department $department): array
{
    $coreBasket = $department->courseBaskets->firstWhere('basket_name', 'Core');
    $electiveBasket = $department->courseBaskets->firstWhere('basket_name', 'Elective');

    return [
        [
            'course_basket_id' => $coreBasket->id,
            'semester_name' => 'I-Sem',
            'sr_no' => 1,
            'course_title' => 'Applied Physics',
            'abbreviation' => 'PHY',
            'course_type' => 'Core',
            'course_code' => '231101',
            'total_iks_hours' => 2,
            'cl' => 2,
            'tl' => 1,
            'll' => 1,
            'self_learning' => 1,
            'credits' => 2,
            'paper_duration' => 2,
            'fa_th_max' => 15,
            'sa_th_max' => 20,
            'theory_min' => 20,
            'fa_pr_max' => 0,
            'fa_pr_min' => 0,
            'sa_pr_max' => 0,
            'sa_pr_min' => 0,
            'sla_max' => 15,
            'sla_min' => 10,
        ],
        [
            'course_basket_id' => $coreBasket->id,
            'semester_name' => 'II-Sem',
            'sr_no' => 2,
            'course_title' => 'Applied Mathematics',
            'abbreviation' => 'MAT',
            'course_type' => 'Core',
            'course_code' => '231102',
            'total_iks_hours' => 0,
            'cl' => 1,
            'tl' => 0,
            'll' => 1,
            'self_learning' => 1,
            'credits' => 2,
            'paper_duration' => 2,
            'fa_th_max' => 10,
            'sa_th_max' => 20,
            'theory_min' => 10,
            'fa_pr_max' => 0,
            'fa_pr_min' => 0,
            'sa_pr_max' => 0,
            'sa_pr_min' => 0,
            'sla_max' => 20,
            'sla_min' => 10,
        ],
        [
            'course_basket_id' => $electiveBasket->id,
            'semester_name' => 'III-Sem',
            'sr_no' => 3,
            'course_title' => 'React JS Technology',
            'abbreviation' => 'RJS',
            'course_type' => 'Elective',
            'course_code' => '235501',
            'total_iks_hours' => 0,
            'cl' => 2,
            'tl' => 0,
            'll' => 2,
            'self_learning' => 1,
            'credits' => 3,
            'paper_duration' => 2,
            'fa_th_max' => 25,
            'sa_th_max' => 0,
            'theory_min' => 0,
            'fa_pr_max' => 0,
            'fa_pr_min' => 0,
            'sa_pr_max' => 25,
            'sa_pr_min' => 10,
            'sla_max' => 25,
            'sla_min' => 10,
        ],
    ];
}

test('cdc user can login and is redirected to cdc dashboard', function () {
    $user = createCdcUser();

    $response = $this->post('/login', [
        'email' => 'cdc@gmail.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('cdc.dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('cdc user can create a HOD account', function () {
    $cdc = createCdcUser();

    $response = $this->actingAs($cdc)->post(route('cdc.users.store'), [
        'name' => 'Role User',
        'email' => 'hod.test@example.com',
        'role' => 'hod',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('cdc.users.index'));
    $response->assertSessionHas('success', 'Hod account created successfully.');

    $this->assertDatabaseHas('users', [
        'email' => 'hod.test@example.com',
        'role' => 'hod',
    ]);
});

test('cdc user can create a moderator account', function () {
    $cdc = createCdcUser();

    $response = $this->actingAs($cdc)->post(route('cdc.users.store'), [
        'name' => 'Moderator User',
        'email' => 'moderator@example.com',
        'role' => 'moderator',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('cdc.users.index'));
    $response->assertSessionHas('success', 'Moderator account created successfully.');

    $this->assertDatabaseHas('users', [
        'email' => 'moderator@example.com',
        'role' => 'moderator',
    ]);
});

test('department user can login through department portal', function () {
    $user = createHodUserForTest();

    $response = $this->post(route('hod.login.submit'), [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('hod.dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('cdc user can create a programme with course baskets', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData());

    $response->assertRedirect(route('cdc.departments.index'));
    $response->assertSessionHas('success', 'Programme created successfully.');

    $this->assertDatabaseHas('departments', [
        'name' => 'Computer Science',
        'code' => 'CS',
        'year' => '2026',
    ]);

    $department = Department::where('name', 'Computer Science')->first();
    expect($department->courseBaskets)->toHaveCount(2);
    expect($department->courseBaskets->first()->hours)->toBe(6);
});

test('programme list shows assignment action and assigned department user', function () {
    $cdc = createCdcUser();
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);

    $response = $this->actingAs($cdc)->get(route('cdc.departments.index'));

    $response->assertOk();
    $response->assertSee('View');
    $response->assertSee('Reassign');
    $response->assertSee('Not started');
    $response->assertSee($hodUser->name);
    $response->assertSee($department->name);
});

test('assign page shows all department accounts', function () {
    $cdc = createCdcUser();
    $department = createProgramme();
    $userOne = createHodUserForTest();
    $userTwo = createHodUserForTest([
        'name' => 'Department User 2',
        'email' => 'department2@example.com',
    ]);

    $response = $this->actingAs($cdc)->get(route('cdc.departments.assign', $department));

    $response->assertOk();
    $response->assertSee($userOne->email);
    $response->assertSee($userTwo->email);
});

test('cdc user can assign a scheme to a department user', function () {
    $cdc = createCdcUser();
    $department = createProgramme();
    $hodUser = createHodUserForTest();

    $response = $this->actingAs($cdc)->post(route('cdc.departments.assign.update', $department), [
        'assigned_user_id' => $hodUser->id,
    ]);

    $response->assertRedirect(route('cdc.departments.index'));
    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'assigned_user_id' => $hodUser->id,
    ]);
});

test('department user sees assigned schemes on dashboard', function () {
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);

    $response = $this->actingAs($hodUser)->get(route('hod.dashboard'));

    $response->assertOk();
    $response->assertSee($department->name);
    $response->assertSee('Design Remaining Courses');
    $response->assertSee('Course Design:');
});

test('non department user cannot access department dashboard', function () {
    $user = createRegularUser();

    $response = $this->actingAs($user)->get(route('hod.dashboard'));

    $response->assertRedirect(route('hod.login'));
    $response->assertSessionHas('error', 'You are not authorized to access the HOD portal.');
});

test('department user can design courses for assigned scheme in excel-style structure', function () {
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);

    $response = $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $response->assertRedirect(route('hod.dashboard'));
    $response->assertSessionHas('success', 'Courses designed successfully for the assigned scheme.');

    $this->assertDatabaseHas('courses', [
        'department_id' => $department->id,
        'course_title' => 'Applied Physics',
        'semester_name' => 'I-Sem',
        'course_code' => null,
        'notional_hours' => 5,
        'theory_total' => 35,
        'total_marks' => 50,
    ]);
});

test('department user can save partially completed courses as draft', function () {
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);
    $draftRows = [validCourseRows($department)[0]];

    $response = $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'save_mode' => 'draft',
        'courses' => $draftRows,
    ]);

    $response->assertRedirect(route('hod.dashboard'));
    $response->assertSessionHas('success', 'Course draft saved successfully. You can continue later.');
    $this->assertDatabaseHas('courses', [
        'department_id' => $department->id,
        'course_title' => 'Applied Physics',
        'course_code' => null,
    ]);
});

test('department user can submit completed courses to cdc for code allocation', function () {
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);

    $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $response = $this->actingAs($hodUser)->post(route('hod.courses.submit', $department));

    $response->assertRedirect(route('hod.dashboard'));
    $response->assertSessionHas('success', 'Courses submitted to CDC for course-code allocation.');
    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'courses_submitted_by_user_id' => $hodUser->id,
    ]);
});

test('cdc user can allocate course codes after department submission', function () {
    $cdc = createCdcUser();
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);

    $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $this->actingAs($hodUser)->post(route('hod.courses.submit', $department));

    $this->actingAs($cdc)->post(route('cdc.departments.approve', $department), [
        'cdc_review_remarks' => 'Design approved.',
    ]);

    $department = $department->fresh('courses');
    $courseCodes = [];

    foreach ($department->courses as $course) {
        $courseCodes[$course->id] = 'CDC-' . str_pad((string) $course->sr_no, 3, '0', STR_PAD_LEFT);
    }

    $response = $this->actingAs($cdc)->post(route('cdc.departments.course-codes.update', $department), [
        'course_codes' => $courseCodes,
    ]);

    $response->assertRedirect(route('cdc.departments.show', $department));
    $response->assertSessionHas('success', 'Course codes allocated successfully.');
    $this->assertDatabaseHas('courses', [
        'department_id' => $department->id,
        'course_title' => 'Applied Physics',
        'course_code' => 'CDC-001',
    ]);
});

test('department edits do not clear cdc assigned course codes', function () {
    $cdc = createCdcUser();
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);

    $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $this->actingAs($hodUser)->post(route('hod.courses.submit', $department));

    $this->actingAs($cdc)->post(route('cdc.departments.approve', $department), [
        'cdc_review_remarks' => 'Design approved.',
    ]);

    $department = $department->fresh('courses');
    $courseCodes = [];

    foreach ($department->courses as $course) {
        $courseCodes[$course->id] = 'CDC-' . str_pad((string) $course->sr_no, 3, '0', STR_PAD_LEFT);
    }

    $this->actingAs($cdc)->post(route('cdc.departments.course-codes.update', $department), [
        'course_codes' => $courseCodes,
    ]);

    $department = $department->fresh('courses');
    $editedRows = validCourseRows($department);
    $editedRows[0]['id'] = $department->courses->sortBy('sr_no')->values()[0]->id;
    $editedRows[0]['course_title'] = 'Applied Physics Updated';
    $editedRows[1]['id'] = $department->courses->sortBy('sr_no')->values()[1]->id;
    $editedRows[2]['id'] = $department->courses->sortBy('sr_no')->values()[2]->id;

    $response = $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => $editedRows,
    ]);

    $response->assertRedirect(route('hod.dashboard'));
    $this->assertDatabaseHas('courses', [
        'department_id' => $department->id,
        'course_title' => 'Applied Physics Updated',
        'course_code' => 'CDC-001',
    ]);
});

test('cdc user can request revision before code allocation', function () {
    $cdc = createCdcUser();
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);

    $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $this->actingAs($hodUser)->post(route('hod.courses.submit', $department));

    $response = $this->actingAs($cdc)->post(route('cdc.departments.request-revision', $department), [
        'cdc_review_remarks' => 'Please revise basket alignment notes.',
    ]);

    $response->assertRedirect(route('cdc.departments.show', $department));
    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'cdc_review_status' => 'revision_requested',
        'cdc_review_remarks' => 'Please revise basket alignment notes.',
    ]);
});

test('cdc user can view scheme details and designed course progress', function () {
    $cdc = createCdcUser();
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);
    $department->courses()->create([
        'course_basket_id' => $department->courseBaskets->first()->id,
        'created_by' => $hodUser->id,
        'semester_name' => 'I-Sem',
        'sr_no' => 1,
        'course_title' => 'Applied Physics',
        'abbreviation' => 'PHY',
        'course_type' => 'Core',
        'course_code' => null,
        'total_iks_hours' => 0,
        'cl' => 2,
        'tl' => 1,
        'll' => 1,
        'self_learning' => 1,
        'notional_hours' => 5,
        'credits' => 2,
        'paper_duration' => 2,
        'fa_th_max' => 15,
        'sa_th_max' => 20,
        'theory_total' => 35,
        'theory_min' => 20,
        'fa_pr_max' => 0,
        'fa_pr_min' => 0,
        'sa_pr_max' => 0,
        'sa_pr_min' => 0,
        'sla_max' => 15,
        'sla_min' => 10,
        'total_marks' => 50,
    ]);

    $response = $this->actingAs($cdc)->get(route('cdc.departments.show', $department));

    $response->assertOk();
    $response->assertSee('Scheme Details');
    $response->assertSee('Applied Physics');
    $response->assertSee($hodUser->name);
    $response->assertSee('Pending CDC allocation');
});

test('department user can view designed courses in read only mode', function () {
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);

    $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $response = $this->actingAs($hodUser)->get(route('hod.courses.show', $department));

    $response->assertOk();
    $response->assertSee('Designed Courses');
    $response->assertSee('Applied Physics');
    $response->assertSee('React JS Technology');
    $response->assertSee('FA-TH Max');
    $response->assertSee('Paper Duration');
});

test('department user cannot add more courses than basket allows', function () {
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);
    $rows = validCourseRows($department);
    $rows[] = array_merge($rows[0], [
        'sr_no' => 4,
        'course_title' => 'Extra Core Course',
        'course_code' => '231199',
    ]);

    $response = $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => $rows,
    ]);

    $response->assertSessionHasErrors('courses');
    $this->assertDatabaseMissing('courses', [
        'department_id' => $department->id,
        'course_title' => 'Extra Core Course',
    ]);
});

test('department user course values must match assigned scheme basket values', function () {
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);
    $rows = validCourseRows($department);
    $rows[0]['cl'] = 5;

    $response = $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => $rows,
    ]);

    $response->assertSessionHasErrors('courses');
});

test('department user cannot design courses for scheme assigned to another user', function () {
    $owner = createHodUserForTest();
    $otherUser = createHodUserForTest([
        'name' => 'Other Department User',
        'email' => 'other.department@example.com',
    ]);
    $department = createProgramme(['assigned_user_id' => $owner->id]);

    $response = $this->actingAs($otherUser)->get(route('hod.courses.edit', $department));

    $response->assertForbidden();
});

test('hod user can login through hod portal', function () {
    $hod = createHodUser();

    $response = $this->post(route('hod.login.submit'), [
        'email' => $hod->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('hod.dashboard'));
    $this->assertAuthenticatedAs($hod);
});

test('faculty user can login through faculty portal', function () {
    $faculty = createFacultyUser();

    $response = $this->post(route('faculty.login.submit'), [
        'email' => $faculty->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('faculty.dashboard'));
    $this->assertAuthenticatedAs($faculty);
});

test('moderator user can login through moderator portal', function () {
    $moderator = createModeratorUser();

    $response = $this->post(route('moderator.login.submit'), [
        'email' => $moderator->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('moderator.dashboard'));
    $this->assertAuthenticatedAs($moderator);
});

test('hod user can assign faculty to designed courses', function () {
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);
    $faculty = createFacultyUser();

    $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $department = $department->fresh('courses');
    $assignments = [];

    foreach ($department->courses as $course) {
        $assignments[$course->id] = $faculty->id;
    }

    $response = $this->actingAs($hodUser)->post(route('hod.faculty-assignments.update', $department), [
        'faculty_assignments' => $assignments,
    ]);

    $response->assertRedirect(route('hod.dashboard'));
    $this->assertDatabaseHas('courses', [
        'id' => $department->courses->first()->id,
        'faculty_user_id' => $faculty->id,
    ]);
});

test('any faculty can be assigned to any programme', function () {
    $hodUser = createHodUserForTest();
    $departmentA = createProgramme(['name' => 'Programme A', 'code' => 'PA', 'assigned_user_id' => $hodUser->id]);
    $faculty = createFacultyUser();

    $this->actingAs($hodUser)->post(route('hod.courses.update', $departmentA), [
        'courses' => validCourseRows($departmentA),
    ]);

    $departmentA = $departmentA->fresh('courses');
    $assignments = [];

    foreach ($departmentA->courses as $course) {
        $assignments[$course->id] = $faculty->id;
    }

    $response = $this->actingAs($hodUser)->post(route('hod.faculty-assignments.update', $departmentA), [
        'faculty_assignments' => $assignments,
    ]);

    $response->assertRedirect(route('hod.dashboard'));
    $this->assertDatabaseHas('courses', [
        'id' => $departmentA->courses->first()->id,
        'faculty_user_id' => $faculty->id,
    ]);
});

test('cdc user can create account without department', function () {
    $cdc = createCdcUser();

    $response = $this->actingAs($cdc)->post(route('cdc.users.store'), [
        'name' => 'New Faculty',
        'email' => 'new.faculty@example.com',
        'role' => 'faculty',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('cdc.users.index'));
    $this->assertDatabaseHas('users', [
        'email' => 'new.faculty@example.com',
        'role' => 'faculty',
        'department_id' => null,
    ]);
});

test('faculty dashboard shows assigned subjects', function () {
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);
    $faculty = createFacultyUser();

    $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $course = $department->fresh('courses')->courses->first();
    $course->update([
        'faculty_user_id' => $faculty->id,
    ]);

    $response = $this->actingAs($faculty)->get(route('faculty.dashboard'));

    $response->assertOk();
    $response->assertSee('Faculty Dashboard');
    $response->assertSee($course->course_title);
});

test('moderator dashboard shows faculty subject queue', function () {
    $moderator = createModeratorUser();
    $hodUser = createHodUserForTest();
    $department = createProgramme(['assigned_user_id' => $hodUser->id]);
    $faculty = createFacultyUser();

    $this->actingAs($hodUser)->post(route('hod.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $course = $department->fresh('courses')->courses->first();
    $course->update([
        'faculty_user_id' => $faculty->id,
    ]);

    $response = $this->actingAs($moderator)->get(route('moderator.dashboard'));

    $response->assertOk();
    $response->assertSee('Moderator Dashboard');
    $response->assertSee('Faculty Subject Queue');
    $response->assertSee($course->course_title);
    $response->assertSee($faculty->name);
});
