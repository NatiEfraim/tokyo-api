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
    ];



    /**
     * Get the inventory record associated with the distribution.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
