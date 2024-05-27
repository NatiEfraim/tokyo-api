<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;



class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected string $guard_name = "passport";



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'is_deleted',
        'emp_type_id',
        'phone',
        'personal_number',
        'email',
        'remember_token',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'is_deleted',
        'created_at',
        'updated_at',
        // 'emp_type_id',
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

   /**
     * Get the employee type associated with the user.
     */
    public function employeeType()
    {
        return $this->belongsTo(EmployeeType::class, 'emp_type_id');
    }

       /**
     * Get the roles for the user.
     */
    // public function roles()
    // {
    //     return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')
    //                 ->where('model_type', self::class);
    // }

  /**
     * Get the translated employee type attribute.
     */

    public function getTranslatedEmployeeTypeAttribute()
    {
        $employeeTypes = [
            'civilian_employee' => 'אע"צ',
            'sadir' => 'סדיר',
            'miluim' => 'מילואים',
            'keva' => 'קבע',
        ];

        return $employeeTypes[$this->employeeType->name] ?? 'חסר';
    }



    /**
     * Translate the user's primary role to Hebrew.
     *
     * @return string|null
     */
    public function translateRoleAttribute()
    {
        $role = $this->roles->first();

        if (!$role) {
            return null;
        }

        $translations = [
            'admin' => 'מנהל',
            'quartermaster' => 'אפסנאי',
            'user' => 'ראש מדור',
        ];

        return $translations[$role->name] ?? $role->name;
    }

        /**
     * Get the distributions for the user.
     */

    public function distribution()
    {
        return $this->hasMany(Distribution::class);
    }

       /**
     * Get the reports for the user.
     */
    public function report()
    {
        return $this->hasMany(Report::class);
    }

}
