<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserConfirmarion;

class ClientUserProfile extends Model
{
    use HasFactory;

    protected $table='client_user_profiles';

    protected $primaryKey = 'UserProfileId';

    protected $fillable=[
        'UserProfileId',
        'ClientId',
        'UserEmail',
        'UserAppId',
        'UserConfirmationId',
    ];

    // 1st 'UserConfirmationId' is the primary key in 'user_confirmations' table.
    // 2nd 'UserConfirmationId' is the foreign key in 'client_user_profiles' table.
    public function userConfirmation(){
        return $this->hasOne(UserConfirmarion::class, 'UserConfirmationId', 'UserConfirmationId');
    }
}
