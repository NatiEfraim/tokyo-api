<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quantity',
        'reserved',
        // 'sku',
        // 'item_type',
        'type_id',
        'detailed_description',
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
        // 'type_id',
        'is_deleted',
        'created_at',
        'updated_at',

    ];


    /**
     * Get the distribution record associated with the inventory.
     */
    public function distribution()
    {
        return $this->hasMany(Distribution::class);
    }


    /**
     * Get the item_type record associated with the distribution.
    */
    public function itemType()
    {
        return $this->belongsTo(ItemType::class,'type_id');
    }
}
