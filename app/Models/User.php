<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Check if the user has the CDC role.
     */
    public function isCdc(): bool
    {
        return $this->role === 'cdc';
    }

    /**
     * Check if the user has the department role.
     */
    public function isDepartment(): bool
    {
        return $this->role === 'department';
    }

    /**
     * Get the schemes assigned to the department user.
     */
    public function assignedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'assigned_user_id');
    }

    /**
     * Get the courses designed by the department user.
     */
    public function designedCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
