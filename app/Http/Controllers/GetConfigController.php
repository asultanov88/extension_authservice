<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientAuth;
use App\Models\ClientServer;

class GetConfigController extends Controller
{
    public function getConfig(Request $request){

        try {
            $clientAuth = ClientAuth::where('AuthKey', '=', $request['RegistrationKey'])->first();

        if($clientAuth){

            $client = Client::where('id', '=', $clientAuth['ClientId'])->first();

            $clientServer = ClientServer::where('ClientId', '=', $client['id'])->first();

            // Making the clientId part of the registrationKey.
            $registrationKey = $clientAuth['AuthKey'].'.'.$client['id'];

            $config = [

                'client' => $client['EntityName'],
                'isAdmin' => $clientAuth['isAdmin'],
                'repositoryServer' => $clientServer['RepositoryServer'],
                'registrationKey' => $registrationKey,
                'token' => $this->generateRegToken($registrationKey),
                'uuid' => $client['uuid'],

            ];

            // Add Jira settings only if user is a JiraUser.
            if($client['JiraUser'] == 1){
                $jiraSettings = $client->clientJiraController;
                $config['jiraSettings'] = [
                    'ClientJiraControllerId' => $jiraSettings->ClientJiraControllerId,
                    'JiraDomain' => $jiraSettings->JiraDomain,
                    'JiraUserName' => $jiraSettings->JiraUserName,
                    'JiraApiKey' => $jiraSettings->JiraApiKey,
                    'JiraIssueType' => $jiraSettings->JiraIssueType,
                ];
            }

            return response()->
            json($config, 200);

        }else{
            return response()->
            json(['error' => 'Requested user not found'], 500);
        }

    } catch (Exception $e) {
            return response()->
            json($e, 500);
        }

    }

    /**
     * The salt is in .env file as REG_KEY_CRYPT_SALT.
     */
    private function generateRegToken($regKey){

        return crypt($regKey, '$5$rounds=5000'.env('REG_KEY_CRYPT_SALT'));

    }
}
