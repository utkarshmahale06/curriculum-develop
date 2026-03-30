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

function createDepartmentUser(array $overrides = []): User
{
    return User::create(array_merge([
        'name' => 'Department User',
        'email' => 'department@example.com',
        'password' => bcrypt('password123'),
        'role' => 'department',
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

test('department user can create account on first login and lands on department dashboard', function () {
    $response = $this->post(route('department.register.submit'), [
        'name' => 'Department User',
        'email' => 'department@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('department.dashboard'));
    $response->assertSessionHas('success', 'Department account created successfully.');

    $this->assertDatabaseHas('users', [
        'email' => 'department@example.com',
        'role' => 'department',
    ]);
});

test('department user can login through department portal', function () {
    $user = createDepartmentUser();

    $response = $this->post(route('department.login.submit'), [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('department.dashboard'));
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
    $departmentUser = createDepartmentUser();
    $department = createProgramme(['assigned_user_id' => $departmentUser->id]);

    $response = $this->actingAs($cdc)->get(route('cdc.departments.index'));

    $response->assertOk();
    $response->assertSee('View');
    $response->assertSee('Reassign');
    $response->assertSee('Not started');
    $response->assertSee($departmentUser->name);
    $response->assertSee($department->name);
});

test('assign page shows all department accounts', function () {
    $cdc = createCdcUser();
    $department = createProgramme();
    $userOne = createDepartmentUser();
    $userTwo = createDepartmentUser([
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
    $departmentUser = createDepartmentUser();

    $response = $this->actingAs($cdc)->post(route('cdc.departments.assign.update', $department), [
        'assigned_user_id' => $departmentUser->id,
    ]);

    $response->assertRedirect(route('cdc.departments.index'));
    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'assigned_user_id' => $departmentUser->id,
    ]);
});

test('department user sees assigned schemes on dashboard', function () {
    $departmentUser = createDepartmentUser();
    $department = createProgramme(['assigned_user_id' => $departmentUser->id]);

    $response = $this->actingAs($departmentUser)->get(route('department.dashboard'));

    $response->assertOk();
    $response->assertSee($department->name);
    $response->assertSee('Design Remaining Courses');
    $response->assertSee('View Designed Courses');
    $response->assertSee('Progress:');
});

test('non department user cannot access department dashboard', function () {
    $user = createRegularUser();

    $response = $this->actingAs($user)->get(route('department.dashboard'));

    $response->assertRedirect(route('department.login'));
    $response->assertSessionHas('error', 'You are not authorized to access the department portal.');
});

test('department user can design courses for assigned scheme in excel-style structure', function () {
    $departmentUser = createDepartmentUser();
    $department = createProgramme(['assigned_user_id' => $departmentUser->id]);

    $response = $this->actingAs($departmentUser)->post(route('department.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $response->assertRedirect(route('department.dashboard'));
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
    $departmentUser = createDepartmentUser();
    $department = createProgramme(['assigned_user_id' => $departmentUser->id]);
    $draftRows = [validCourseRows($department)[0]];

    $response = $this->actingAs($departmentUser)->post(route('department.courses.update', $department), [
        'save_mode' => 'draft',
        'courses' => $draftRows,
    ]);

    $response->assertRedirect(route('department.dashboard'));
    $response->assertSessionHas('success', 'Course draft saved successfully. You can continue later.');
    $this->assertDatabaseHas('courses', [
        'department_id' => $department->id,
        'course_title' => 'Applied Physics',
        'course_code' => null,
    ]);
});

test('department user can submit completed courses to cdc for code allocation', function () {
    $departmentUser = createDepartmentUser();
    $department = createProgramme(['assigned_user_id' => $departmentUser->id]);

    $this->actingAs($departmentUser)->post(route('department.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $response = $this->actingAs($departmentUser)->post(route('department.courses.submit', $department));

    $response->assertRedirect(route('department.dashboard'));
    $response->assertSessionHas('success', 'Courses submitted to CDC for course-code allocation.');
    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'courses_submitted_by_user_id' => $departmentUser->id,
    ]);
});

test('cdc user can allocate course codes after department submission', function () {
    $cdc = createCdcUser();
    $departmentUser = createDepartmentUser();
    $department = createProgramme(['assigned_user_id' => $departmentUser->id]);

    $this->actingAs($departmentUser)->post(route('department.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $this->actingAs($departmentUser)->post(route('department.courses.submit', $department));

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

test('cdc user can view scheme details and designed course progress', function () {
    $cdc = createCdcUser();
    $departmentUser = createDepartmentUser();
    $department = createProgramme(['assigned_user_id' => $departmentUser->id]);
    $department->courses()->create([
        'course_basket_id' => $department->courseBaskets->first()->id,
        'created_by' => $departmentUser->id,
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
    $response->assertSee($departmentUser->name);
    $response->assertSee('Pending CDC allocation');
});

test('department user can view designed courses in read only mode', function () {
    $departmentUser = createDepartmentUser();
    $department = createProgramme(['assigned_user_id' => $departmentUser->id]);

    $this->actingAs($departmentUser)->post(route('department.courses.update', $department), [
        'courses' => validCourseRows($department),
    ]);

    $response = $this->actingAs($departmentUser)->get(route('department.courses.show', $department));

    $response->assertOk();
    $response->assertSee('Designed Courses');
    $response->assertSee('Applied Physics');
    $response->assertSee('React JS Technology');
    $response->assertSee('FA-TH Max');
    $response->assertSee('Paper Duration');
});

test('department user cannot add more courses than basket allows', function () {
    $departmentUser = createDepartmentUser();
    $department = createProgramme(['assigned_user_id' => $departmentUser->id]);
    $rows = validCourseRows($department);
    $rows[] = array_merge($rows[0], [
        'sr_no' => 4,
        'course_title' => 'Extra Core Course',
        'course_code' => '231199',
    ]);

    $response = $this->actingAs($departmentUser)->post(route('department.courses.update', $department), [
        'courses' => $rows,
    ]);

    $response->assertSessionHasErrors('courses');
    $this->assertDatabaseMissing('courses', [
        'department_id' => $department->id,
        'course_title' => 'Extra Core Course',
    ]);
});

test('department user course values must match assigned scheme basket values', function () {
    $departmentUser = createDepartmentUser();
    $department = createProgramme(['assigned_user_id' => $departmentUser->id]);
    $rows = validCourseRows($department);
    $rows[0]['cl'] = 5;

    $response = $this->actingAs($departmentUser)->post(route('department.courses.update', $department), [
        'courses' => $rows,
    ]);

    $response->assertSessionHasErrors('courses');
});

test('department user cannot design courses for scheme assigned to another user', function () {
    $owner = createDepartmentUser();
    $otherUser = createDepartmentUser([
        'name' => 'Other Department User',
        'email' => 'other.department@example.com',
    ]);
    $department = createProgramme(['assigned_user_id' => $owner->id]);

    $response = $this->actingAs($otherUser)->get(route('department.courses.edit', $department));

    $response->assertForbidden();
});
