<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        // 'sku',
        'icon_number',
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
     * Get the inventory record associated with the inventory.
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class,'type_id');
    }

    /**
     * Get the distribution record associated with the inventory.
     */
    public function distribution()
    {
        return $this->hasMany(Distribution::class);
    }



}
