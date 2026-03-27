<?php

use App\Models\Department;
use App\Models\CourseBasket;
use App\Models\User;

// Helper to create a CDC user
function createCdcUser(): User
{
    return User::create([
        'name' => 'CDC Admin',
        'email' => 'cdc@gmail.com',
        'password' => bcrypt('password'),
        'role' => 'cdc',
    ]);
}

// Helper to create a non-CDC user
function createRegularUser(): User
{
    return User::create([
        'name' => 'Regular User',
        'email' => 'user@gmail.com',
        'password' => bcrypt('password'),
        'role' => null,
    ]);
}

// Helper: valid basket data
function validBasketData(): array
{
    return [
        ['basket_name' => 'Core', 'courses' => 5, 'cl' => 3, 'tl' => 1, 'll' => 2, 'credits' => 4, 'marks' => 100],
    ];
}

// Helper: valid programme store payload
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

// ===========================
// Phase-1 Tests (auth)
// ===========================

test('cdc user can login and is redirected to cdc dashboard', function () {
    $user = createCdcUser();

    $response = $this->post('/login', [
        'email' => 'cdc@gmail.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('cdc.dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('non cdc user is blocked from accessing cdc dashboard', function () {
    $user = createRegularUser();
    $this->actingAs($user);

    $response = $this->get('/cdc/dashboard');

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'You are not authorized to access this page.');
});

test('unauthenticated user is redirected to login', function () {
    $response = $this->get('/cdc/dashboard');
    $response->assertRedirect(route('login'));
});

test('cdc dashboard loads with action buttons', function () {
    $user = createCdcUser();
    $response = $this->actingAs($user)->get('/cdc/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Create Programme');
    $response->assertSee('View Programmes');
});

test('all cdc routes are accessible by cdc user', function () {
    $user = createCdcUser();

    $this->actingAs($user)->get('/cdc/dashboard')->assertStatus(200);
    $this->actingAs($user)->get('/cdc/departments')->assertStatus(200);
    $this->actingAs($user)->get('/cdc/departments/create')->assertStatus(200);
});

// ===========================
// Programme CRUD Tests
// ===========================

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

    $dept = Department::where('name', 'Computer Science')->first();
    expect($dept->courseBaskets)->toHaveCount(1);
    expect($dept->courseBaskets->first()->hours)->toBe(6); // 3+1+2
    expect($dept->courseBaskets->first()->basket_name)->toBe('Core');
});

test('duplicate programme name is prevented', function () {
    $user = createCdcUser();
    Department::create(['name' => 'Computer Science', 'code' => 'CS', 'year' => '2026']);

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'code' => 'CS2',
    ]));

    $response->assertSessionHasErrors('name');
});

test('duplicate programme code is prevented', function () {
    $user = createCdcUser();
    Department::create(['name' => 'Computer Science', 'code' => 'CS', 'year' => '2026']);

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'name' => 'Electronics',
    ]));

    $response->assertSessionHasErrors('code');
});

test('year is required', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'year' => '',
    ]));

    $response->assertSessionHasErrors('year');
});

test('programme name is required', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'name' => '',
    ]));

    $response->assertSessionHasErrors('name');
});

test('programme code is required', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'code' => '',
    ]));

    $response->assertSessionHasErrors('code');
});

test('at least one basket is required', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', [
        'name' => 'Test',
        'code' => 'TST',
        'year' => '2026',
        'baskets' => [],
    ]);

    $response->assertSessionHasErrors('baskets');
});

test('programme list page shows programmes with year', function () {
    $user = createCdcUser();
    $dept = Department::create(['name' => 'Computer Science', 'code' => 'CS', 'year' => '2026']);
    $dept->courseBaskets()->create([
        'basket_name' => 'Core', 'courses' => 5,
        'cl' => 3, 'tl' => 1, 'll' => 2, 'hours' => 6, 'credits' => 4, 'marks' => 100,
    ]);

    $response = $this->actingAs($user)->get('/cdc/departments');

    $response->assertStatus(200);
    $response->assertSee('Computer Science');
    $response->assertSee('2026');
});

// ===========================
// New: Basket Name, Numeric Courses, Nullable CL/TL/LL
// ===========================

test('create page shows Basket Name column header', function () {
    $user = createCdcUser();
    $response = $this->actingAs($user)->get('/cdc/departments/create');

    $response->assertStatus(200);
    $response->assertSee('Basket Name');
    $response->assertDontSee('Level Name');
});

test('courses field does not accept text values', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'baskets' => [
            ['basket_name' => 'Core', 'courses' => 'abc', 'cl' => 3, 'tl' => 1, 'll' => 2, 'credits' => 4, 'marks' => 100],
        ],
    ]));

    $response->assertSessionHasErrors('baskets.0.courses');
});

test('courses field accepts numeric values', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'baskets' => [
            ['basket_name' => 'Core', 'courses' => 5, 'cl' => 3, 'tl' => 1, 'll' => 2, 'credits' => 4, 'marks' => 100],
        ],
    ]));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('cdc.departments.index'));
});

test('CL can be empty and treated as 0', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'baskets' => [
            ['basket_name' => 'Core', 'courses' => 5, 'cl' => null, 'tl' => 1, 'll' => 2, 'credits' => 4, 'marks' => 100],
        ],
    ]));

    $response->assertSessionHasNoErrors();
    $dept = Department::where('name', 'Computer Science')->first();
    expect($dept->courseBaskets->first()->hours)->toBe(3); // 0+1+2
    expect($dept->courseBaskets->first()->cl)->toBeNull();
});

test('TL can be empty and treated as 0', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'baskets' => [
            ['basket_name' => 'Core', 'courses' => 5, 'cl' => 3, 'tl' => null, 'll' => 2, 'credits' => 4, 'marks' => 100],
        ],
    ]));

    $response->assertSessionHasNoErrors();
    $dept = Department::where('name', 'Computer Science')->first();
    expect($dept->courseBaskets->first()->hours)->toBe(5); // 3+0+2
    expect($dept->courseBaskets->first()->tl)->toBeNull();
});

test('LL can be empty and treated as 0', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'baskets' => [
            ['basket_name' => 'Core', 'courses' => 5, 'cl' => 3, 'tl' => 1, 'll' => null, 'credits' => 4, 'marks' => 100],
        ],
    ]));

    $response->assertSessionHasNoErrors();
    $dept = Department::where('name', 'Computer Science')->first();
    expect($dept->courseBaskets->first()->hours)->toBe(4); // 3+1+0
    expect($dept->courseBaskets->first()->ll)->toBeNull();
});

test('hours calculates correctly when all CL TL LL are empty', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'baskets' => [
            ['basket_name' => 'Core', 'courses' => 5, 'cl' => null, 'tl' => null, 'll' => null, 'credits' => 4, 'marks' => 100],
        ],
    ]));

    $response->assertSessionHasNoErrors();
    $dept = Department::where('name', 'Computer Science')->first();
    expect($dept->courseBaskets->first()->hours)->toBe(0);
});

test('basket_name is required', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'baskets' => [
            ['basket_name' => '', 'courses' => 5, 'cl' => 3, 'tl' => 1, 'll' => 2, 'credits' => 4, 'marks' => 100],
        ],
    ]));

    $response->assertSessionHasErrors('baskets.0.basket_name');
});

test('basket fields validation messages are user friendly', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'baskets' => [
            ['basket_name' => '', 'courses' => 'abc', 'cl' => 'x', 'tl' => 'y', 'll' => 'z', 'credits' => '', 'marks' => ''],
        ],
    ]));

    $errors = session('errors');
    expect($errors->first('baskets.0.basket_name'))->toBe('Basket name is required.');
    expect($errors->first('baskets.0.courses'))->toBe('Courses must be a number.');
    expect($errors->first('baskets.0.cl'))->toBe('CL must be numeric.');
    expect($errors->first('baskets.0.tl'))->toBe('TL must be numeric.');
    expect($errors->first('baskets.0.ll'))->toBe('LL must be numeric.');
});

// ===========================
// Year Format Validation Tests
// ===========================

test('year does not accept text values', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'year' => 'abcd',
    ]));

    $response->assertSessionHasErrors('year');
});

test('year rejects values outside 2000-2100 range', function () {
    $user = createCdcUser();

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'year' => '1999',
    ]));

    $response->assertSessionHasErrors('year');

    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'name' => 'Test2', 'code' => 'T2',
        'year' => '2101',
    ]));

    $response->assertSessionHasErrors('year');
});

test('year shows user friendly error messages', function () {
    $user = createCdcUser();

    // Empty year
    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'year' => '',
    ]));
    expect(session('errors')->first('year'))->toBe('Year is required.');

    // Text year
    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'year' => 'abc',
    ]));
    expect(session('errors')->first('year'))->toBe('Enter a valid year.');

    // 2-digit year
    $response = $this->actingAs($user)->post('/cdc/departments/store', validProgrammeData([
        'year' => '25',
    ]));
    expect(session('errors')->first('year'))->toBe('Year must be 4 digits.');
});
