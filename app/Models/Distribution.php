<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'comment',
        'status',
        'quantity',
        'inventory_id',
        'department_id',
        'created_by',
        'is_deleted',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'is_deleted',

        // 'inventory_id',
        // 'department_id',
        // 'created_by',

        'created_at',
        'updated_at'
    ];


    /**
     * Get the depratment record associated with the distribution.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the inventory record associated with the distribution.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }


    //? set relations function with users table
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by')
            ->select('id', 'name', 'emp_type_id', 'phone', 'email', 'personal_number')->with('employeeType');
    }

    public function createdForUser()
    {
        return $this->belongsTo(User::class, 'created_for')
        ->select('id', 'name', 'emp_type_id', 'phone', 'email', 'personal_number')->with('employeeType');
    }

    // public function updatedByUser()
    // {
    //     return $this->belongsTo(User::class, 'updated_by')
    //         ->select('id', 'name', 'emp_type_id', 'phone')->with(['employeeType']);
    // }



    /**
     * Get the translated status for the distribution.
     *
     * @return string
     */


    public function getStatusTranslation()
    {
        $translations = [
            0 => 'ממתין למשיכה',
            1 =>  'אושר',
            2 => 'בוטל',
        ];

        return $translations[$this->status] ?? 'לא מוגדר';
    }

}
