<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Department extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'year',
        'award_class_subjects',
        'assigned_user_id',
        'courses_submitted_to_cdc_at',
        'courses_submitted_by_user_id',
        'course_codes_assigned_at',
        'course_codes_assigned_by_user_id',
    ];

    /**
     * Get the course baskets for this department/programme.
     */
    public function courseBaskets(): HasMany
    {
        return $this->hasMany(CourseBasket::class);
    }

    /**
     * Get the department user assigned to this scheme.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get the HOD user linked to this scheme.
     */
    public function hodUsers(): HasMany
    {
        return $this->hasMany(User::class, 'department_id')->where('role', 'hod');
    }

    /**
     * Get the faculty users linked to this scheme.
     */
    public function facultyUsers(): HasMany
    {
        return $this->hasMany(User::class, 'department_id')->where('role', 'faculty');
    }

    /**
     * Get the department user who submitted the courses to CDC.
     */
    public function courseSubmittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courses_submitted_by_user_id');
    }

    /**
     * Get the CDC user who assigned the course codes.
     */
    public function courseCodesAssignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'course_codes_assigned_by_user_id');
    }

    /**
     * Get the designed courses for this scheme.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Determine whether the designed courses have been submitted to CDC.
     */
    public function hasSubmittedCoursesToCdc(): bool
    {
        if (Schema::hasColumn('departments', 'courses_submitted_to_cdc_at') && $this->courses_submitted_to_cdc_at) {
            return true;
        }

        $courses = $this->relationLoaded('courses') ? $this->courses : $this->courses()->get();

        return $courses->isNotEmpty()
            && (
                $courses->every(fn (Course $course) => Str::startsWith((string) $course->course_code, 'SUBMITTED-'))
                || $courses->contains(function (Course $course) {
                    $code = (string) $course->course_code;

                    return $code !== ''
                        && ! Str::startsWith($code, ['DRAFT-', 'SUBMITTED-', 'PENDING-']);
                })
            );
    }

    /**
     * Determine whether CDC has assigned final course codes.
     */
    public function hasAssignedCourseCodes(): bool
    {
        if (Schema::hasColumn('departments', 'course_codes_assigned_at') && $this->course_codes_assigned_at) {
            return true;
        }

        $courses = $this->relationLoaded('courses') ? $this->courses : $this->courses()->get();

        return $courses->contains(function (Course $course) {
                $code = (string) $course->course_code;

                return $code !== ''
                    && ! Str::startsWith($code, ['DRAFT-', 'SUBMITTED-', 'PENDING-']);
            });
    }

    /**
     * Cast timestamp workflow fields.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'courses_submitted_to_cdc_at' => 'datetime',
            'course_codes_assigned_at' => 'datetime',
        ];
    }
}
