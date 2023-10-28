<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'title', 'description', 'goal_amount', 'current_amount'
    ];

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }
}
