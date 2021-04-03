<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pet_id',
        'quantity',
        'ship_date',
        'status',
        'complete'
    ];

    // Relations
    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    // Accessors
    public function getCompleteAttribute($value)
    {
        return boolval($value);
    }

    public function getShipDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i:s.u');
    }
}
