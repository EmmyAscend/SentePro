<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'trading_name',
        'registration_number',
        'country',
        'phone',
        'email',
        'industry',
        'expected_monthly_volume',
        'business_description',
    ];
}
