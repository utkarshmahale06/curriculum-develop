<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    /**
     * Get the course baskets for this department/programme.
     */
    public function courseBaskets(): HasMany
    {
        return $this->hasMany(CourseBasket::class);
    }
}
