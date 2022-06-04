<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    // 'id' is the primary key in 'client' table.
    // 'ClientId' is the foreign key in 'client_jira_controllers' table.
    public function clientJiraController(){
        return $this->hasOne(ClientJiraController::class, 'ClientId', 'id');
    }
}
