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
        'department_id',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'is_deleted',
        'created_at',
        'updated_at',
        'emp_type_id',



    ];

    
    public function employeeType()
    {
        return $this->belongsTo(EmployeeType::class, 'emp_type_id');
    }

    
    public function distribution()
    {
        return $this->hasMany(Distribution::class);
    }


        /**
     * Get the depratment record associated with the distribution.
     */
    public function department()
    {
        return $this->belongsTo(Department::class,'department_id');
    }

    /**
     * Get the translated employee type attribute.
     *
     * @return string
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


}