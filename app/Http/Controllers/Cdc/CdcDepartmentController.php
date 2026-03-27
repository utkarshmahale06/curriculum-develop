<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\CourseBasket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CdcDepartmentController extends Controller
{
    /**
     * Display a listing of departments/programmes.
     */
    public function index()
    {
        $departments = Department::with('courseBaskets')->orderBy('id', 'asc')->get();

        return view('cdc.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department/programme.
     */
    public function create()
    {
        return view('cdc.departments.create');
    }

    /**
     * Store a newly created department/programme with course baskets.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                    => ['required', 'string', 'max:255', 'unique:departments,name'],
            'code'                    => ['required', 'string', 'max:255', 'unique:departments,code'],
            'year'                    => ['required', 'numeric', 'digits:4', 'min:2000', 'max:2100'],
            'award_class_subjects'    => ['required', 'integer', 'min:0'],
            'baskets'                 => ['required', 'array', 'min:1'],
            'baskets.*.basket_name'   => ['required', 'string', 'max:255'],
            'baskets.*.courses'       => ['required', 'numeric', 'min:0'],
            'baskets.*.cl'            => ['nullable', 'numeric', 'min:0'],
            'baskets.*.tl'            => ['nullable', 'numeric', 'min:0'],
            'baskets.*.ll'            => ['nullable', 'numeric', 'min:0'],
            'baskets.*.credits'       => ['required', 'integer', 'min:0'],
            'baskets.*.marks'         => ['required', 'integer', 'min:0'],
        ], [
            'name.required'                   => 'Program name is required.',
            'name.unique'                     => 'A programme with this name already exists.',
            'code.required'                   => 'Program code is required.',
            'code.unique'                     => 'Program code must be unique.',
            'year.required'                   => 'Year is required.',
            'year.numeric'                    => 'Enter a valid year.',
            'year.digits'                     => 'Year must be 4 digits.',
            'year.min'                        => 'Enter a valid year.',
            'year.max'                        => 'Enter a valid year.',
            'award_class_subjects.required'   => 'Number of award class subjects is required.',
            'award_class_subjects.integer'    => 'Number of award class subjects must be a whole number.',
            'award_class_subjects.min'        => 'Number of award class subjects cannot be negative.',
            'baskets.required'                => 'At least one course basket is required.',
            'baskets.min'                     => 'At least one course basket is required.',
            'baskets.*.basket_name.required'  => 'Basket name is required.',
            'baskets.*.courses.required'      => 'Courses is required for all baskets.',
            'baskets.*.courses.numeric'       => 'Courses must be a number.',
            'baskets.*.cl.numeric'            => 'CL must be numeric.',
            'baskets.*.tl.numeric'            => 'TL must be numeric.',
            'baskets.*.ll.numeric'            => 'LL must be numeric.',
            'baskets.*.credits.required'      => 'Credits is required for all baskets.',
            'baskets.*.credits.integer'       => 'Credits must be a number.',
            'baskets.*.marks.required'        => 'Marks is required for all baskets.',
            'baskets.*.marks.integer'         => 'Marks must be a number.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $department = Department::create([
                    'name' => $request->name,
                    'code' => $request->code,
                    'year' => $request->year,
                    'award_class_subjects' => $request->award_class_subjects,
                ]);

                foreach ($request->baskets as $basket) {
                    $cl = (int) ($basket['cl'] ?? 0);
                    $tl = (int) ($basket['tl'] ?? 0);
                    $ll = (int) ($basket['ll'] ?? 0);

                    $department->courseBaskets()->create([
                        'basket_name' => $basket['basket_name'],
                        'courses'     => (int) $basket['courses'],
                        'cl'          => $basket['cl'] !== null && $basket['cl'] !== '' ? (int) $basket['cl'] : null,
                        'tl'          => $basket['tl'] !== null && $basket['tl'] !== '' ? (int) $basket['tl'] : null,
                        'll'          => $basket['ll'] !== null && $basket['ll'] !== '' ? (int) $basket['ll'] : null,
                        'hours'       => $cl + $tl + $ll,
                        'credits'     => (int) $basket['credits'],
                        'marks'       => (int) $basket['marks'],
                    ]);
                }
            });

            return redirect()->route('cdc.departments.index')
                ->with('success', 'Programme created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'An error occurred while creating the programme. Please try again.');
        }
    }
}
