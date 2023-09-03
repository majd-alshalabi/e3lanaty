<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'deleted',
        'about_me',
        'location',
        'image',
        'rate',
        'blocked',
        'type_of_account',
        'admin',
        'point',
        'deleted',
        'code',
    ];

    protected $hidden = [
        'password',
        'code',
    ];

}
