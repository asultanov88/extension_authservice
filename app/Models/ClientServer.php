<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientServer extends Model
{
    use HasFactory;

    protected $table = 'client_server';

    protected $fillable = [
        'ClientId',
        'RepositoryServer',
    ];
}
