<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ClientJiraController;
use App\Models\ClientUserProfile;

class Client extends Model
{
    use HasFactory;

    protected $table = 'client';

    protected $fillable = [
        'uuid',
        'EntityName',
        'Email',
        'PhoneNumber',
        'Address1',
        'Address2',
        'County',
        'State',
        'Zip',
        'Country',
        'JiraUser',
    ];

    // 'ClientId' is the foreign key in 'client_jira_controllers' table.
    // 'id' is the primary key in 'client' table.
    public function clientJiraController(){
        return $this->hasOne(ClientJiraController::class, 'ClientId', 'id');
    }

    // 'ClientId' is the foreign key in 'client_user_profiles' table.
    // 'id' is the primary key in 'client' table.
    public function users(){
        return $this->hasMany(ClientUserProfile::class, 'ClientId', 'id');
    }
}
