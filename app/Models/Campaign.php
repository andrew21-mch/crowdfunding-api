<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title', 'description', 'goal_amount', 'current_amount', 'user_id'
    ];

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
