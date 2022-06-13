<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserConfirmarion extends Model
{
    use HasFactory;

    protected $table='user_confirmations';

    protected $primaryKey = 'UserConfirmationId';

    protected $fillable=[
        'UserConfirmationId',
        'UserConfirmationCode',
        'NewAppId',
    ];
}
