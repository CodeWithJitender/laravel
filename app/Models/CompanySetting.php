<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'company_code',
        'company_logo',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website',
        'email',
        'phone',
        'tax_number',
        'registration_number',
    ];
}
