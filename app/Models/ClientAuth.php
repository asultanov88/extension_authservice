<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientAuth extends Model
{
    use HasFactory;

    protected $table = 'client_authkey';

    protected $fillable = [
        'ClientId',
        'AuthKey',
        'ExpirationDate',
    ];
}
