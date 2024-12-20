<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hour',
        'last_quantity',
        'new_quantity',
        'sku',
        'inventory_id',
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
        'created_at',
        'updated_at',
        
    ];


        /**
     * Get the user record associated with the reports.
     */

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by')
            ->select('id', 'name');
    }


        /**
     * Get the inventory record associated with the reports.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class,'inventory_id')->with(['itemType']);
    }

}