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
        'general_comment',
        'inventory_comment',
        'order_number',
        'status',
        'total_quantity',
        'quantity_per_item',
        'inventory_id',
        'type_id',
        'year',
        'inventory_items',
        'department_id',
        'created_by',
        'created_for',
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

        // 'inventory_id',
        // 'department_id',
        // 'created_by',
        // 'created_for',

        'is_deleted',
        'created_at',
        'updated_at'
    ];


    /**
     * Get the depratment record associated with the distribution.
     */
    public function department()
    {
        return $this->belongsTo(Department::class,'department_id');
    }

    /**
     * Get the inventory record associated with the distribution.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class)->with(['itemType']);
    }

    /**
     * Get the item_type record associated with the distribution.
     */
    public function itemType()
    {
        return $this->belongsTo(ItemType::class, 'type_id');
    }

    //? set relations function with users table
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function createdForUser()
    {
        return $this->belongsTo(Client::class, 'created_for')
            ->select('id', 'name', 'emp_type_id', 'phone', 'email', 'personal_number')->with(['employeeType']);
    }




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
            3 => 'נאסף',
        ];

        return $translations[$this->status] ?? 'לא מוגדר';
    }

}
