<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;




class User extends Authenticatable
{
    use HasFactory, Notifiable , HasApiTokens;

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


    public function employeeType()
    {
        return $this->belongsTo(EmployeeType::class, 'emp_type_id');
    }

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


    public function distribution()
    {
        return $this->hasMany(Distribution::class);
    }


}
