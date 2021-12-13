<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'client';

    protected $fillable = [
        'EntityName',
        'Email',
        'PhoneNumber',
        'Address1',
        'Address2',
        'County',
        'State',
        'Zip',
        'Country',
    ];
}
