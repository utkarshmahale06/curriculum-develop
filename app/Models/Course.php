<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'course_basket_id',
        'created_by',
        'created_by_user_id',
        'semester_name',
        'sr_no',
        'course_title',
        'abbreviation',
        'course_type',
        'course_code',
        'total_iks_hours',
        'cl',
        'tl',
        'll',
        'hours',
        'self_learning',
        'notional_hours',
        'credits',
        'paper_duration',
        'fa_th_max',
        'sa_th_max',
        'theory_total',
        'theory_min',
        'fa_pr_max',
        'fa_pr_min',
        'sa_pr_max',
        'sa_pr_min',
        'sla_max',
        'sla_min',
        'marks',
        'total_marks',
        'faculty_user_id',
    ];

    /**
     * Get the scheme this course belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the basket this course is mapped to.
     */
    public function courseBasket(): BelongsTo
    {
        return $this->belongsTo(CourseBasket::class);
    }

    /**
     * Get the department user who created the course row.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the faculty user assigned by HOD.
     */
    public function assignedFaculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_user_id');
    }
}
