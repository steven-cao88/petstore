<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'value',
        'expired_at',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Query Scopes
    public function scopeValid($query)
    {
        return $query->whereDate('expired_at', '>', Carbon::now());
    }
}
