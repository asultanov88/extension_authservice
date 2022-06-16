<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientAuth;
use App\Models\ClientServer;
use App\Models\UserConfirmarion;

class GetConfigController extends Controller
{
    public function getConfig(Request $request){
        $request->validate([
            'RegistrationKey' => 'required|string|exists:client_authkey,AuthKey',
            'UserEmail' => 'required|string|exists:client_user_profiles,UserEmail',
            'UserAppId' => 'required|string',
        ]);

        try {
            $regKeyAuth = ClientAuth::where('AuthKey','=',$request['RegistrationKey'])
                ->join('client', 'client.id','=','client_authkey.ClientId')
                ->first(
                    array(                                      
                        'client.id'                                     
                      )
                );

            // Must be accessible from the else closure.
            $user = null;

            if($regKeyAuth && isset($regKeyAuth['id'])){

                $client = Client::where('id','=',$regKeyAuth['id'])->first();
                $user = $client->users->where('UserEmail','=',$request['UserEmail'])->first();
                $clientAuth = $client->auths->where('AuthKey','=',$request['RegistrationKey'])->first();
                $clientServer = $client->server;

                if($user && $user['UserAppId'] == $request['UserAppId']){
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
                            $clientJira = $client->clientJiraController;
                            $config['jiraSettings'] = [
                                'ClientJiraControllerId' => $clientJira->ClientJiraControllerId,
                                'JiraDomain' => $clientJira->JiraDomain,
                                'JiraUserName' => $clientJira->JiraUserName,
                                'JiraApiKey' => $clientJira->JiraApiKey,
                                'JiraIssueType' => $clientJira->JiraIssueType,
                            ];
                        }    
                        return response()->
                        json($config, 200); 

                }else if($user && $user['UserAppId'] != $request['UserAppId']){
                    // Delete previous confirmation records.
                    $previousUserConfirmation = $user->userConfirmation;
                    if($previousUserConfirmation){
                        $user->update([
                            'UserConfirmationId' => null
                        ]);
                        $previousUserConfirmation->delete();
                    }

                    $userConfirmation = new UserConfirmarion();
                    $userConfirmation['NewAppId'] = $request['UserAppId'];
                    $userConfirmation['UserConfirmationCode'] = $this->generateConfirmationCode();
                    $userConfirmation->save();

                    // Updating the user profile with the new UserConfirmationId.
                    $user->update([
                        'UserConfirmationId' => $userConfirmation['UserConfirmationId']
                    ]);

                    // TODO: Send email to user with the confirmation code;
                    return response()->
                    json(['result' => ['status'=>'Unauthorized','message'=>'Confirmation sent']], 200);
                }else{
                    // UserEmail does not match.
                    return response()->
                    json(['error' => 'Unauthorized'], 401);
                }

            }else{
                // RegistrationKey does not match.
                return response()->
                json(['error' => 'Unauthorized'], 401);
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

    /**
     * Generates confirmation code.
     */
    private function generateConfirmationCode(){
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 8);
    }
}
