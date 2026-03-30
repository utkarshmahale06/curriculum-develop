<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseBasket extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'basket_name',
        'courses',
        'cl',
        'tl',
        'll',
        'hours',
        'credits',
        'marks',
    ];

    /**
     * Get the department/programme this basket belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the designed courses mapped to this basket.
     */
    public function designedCourses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
