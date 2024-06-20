<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeType extends Model
{
    use HasFactory;

    // protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    protected $hidden = ['created_at', 'updated_at', 'is_deleted'];

    //set the relation
    public function users()
    {
        return $this->hasMany(User::class, 'emp_type_id');
    }

    //set the relation
    public function client()
    {
        return $this->hasMany(Client::class, 'emp_type_id');
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

        return $employeeTypes[$this->name] ?? 'חסר';
    }
}
