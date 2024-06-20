<?php

namespace App\Models;

use App\Enums\DistributionStatus;
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
        'user_comment',
        'admin_comment',
        'quartermaster_comment',
        'type_comment',
        'order_number',
        'status',
        'total_quantity',
        'quantity_per_item',
        'sku',
        'quantity_per_inventory',
        'type_id',
        'created_by',
        'created_for',
        'quartermaster_id',
        'is_deleted',
        'created_at',
        'updated_at',
        'inventory_id',
        'quantity_approved',
        'canceled_reason',

        //! need to remove
        // 'year',
        // 'inventory_items',
        // 'department_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
        
        'type_id',
        'created_by',
        'created_for',
        'quartermaster_id',
        
        'is_deleted',
        'created_at',
        'updated_at',
        // 'inventory_id',


        // //! need to remove
        // 'department_id',
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
        return $this->belongsTo(Inventory::class, 'inventory_id')->with(['itemType']);
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

    /**
     * Get the user record associated with the distribution.
     */
    public function createdForUser()
    {
        return $this->belongsTo(Client::class, 'created_for')
            ->select('id', 'name', 'emp_type_id', 'phone', 'email', 'personal_number', 'department_id')
            ->with(['employeeType', 'department']);
    }

    //? fetch associated quartermaster user records 
    public function quartermaster()
    {
        return $this->belongsTo(Client::class, 'quartermaster_id')
            ->select('id', 'name', 'emp_type_id', 'phone', 'email', 'personal_number', 'department_id')->with(['employeeType', 'department']);
    }




    /**
     * Get the translated status for the distribution.
     *
     * @return string
     */


    public function getStatusTranslation()
    {
        $translations = [
            DistributionStatus::PENDING->value => 'ממתין למשיכה',
            DistributionStatus::APPROVED->value =>  'אושר',
            DistributionStatus::CANCELD->value => 'בוטל',
            DistributionStatus::COLLECTED->value => 'נאסף',
        ];

        return $translations[$this->status] ?? 'לא מוגדר';
    }

}