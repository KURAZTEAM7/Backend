<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_name',
        'phone_number',
        'email',
        'logo',
        'zone',
        'region',
        'google_map_location',
        'website',
        'telegram',
        'whatsapp',
        'tin_number',
        'license',
        'logo_public_id',
        'license_public_id',
        'description',
    ];  // TODO: check if logo_public_id needs to be here
}
