<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientJiraController extends Model
{
    use HasFactory;

    protected $table='client_jira_controllers';

    protected $fillable=[
        'ClientId',
        'ClientJiraControllerId',
        'JiraDomain',
        'JiraUserName',
        'JiraApiKey',
        'JiraIssueType',
    ];

    // 'ClientId' is the foreign key in 'client_jira_controllers' table.
    public function bug(){
        return $this->belongsTo(Client::class, 'ClientId');
    }
}
