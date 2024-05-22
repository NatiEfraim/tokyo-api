<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;


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


}
