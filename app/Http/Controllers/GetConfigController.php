<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientAuth;
use App\Models\ClientServer;
use App\Models\UserConfirmarion;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationConfirmation;

class GetConfigController extends Controller
{
    /**
     * Confirms regostration code.
     */
    public function confirmUserRegistrationCode(Request $request){
        $request->validate([
            'RegistrationKey' => 'required|string',
            'UserEmail' => 'required|string',
            'UserAppId' => 'required|string',
            'ConfirmationCode' => 'required|string',
        ]);

        try {
            $regKeyAuth = ClientAuth::where('AuthKey','=',$request['RegistrationKey'])
            ->join('client', 'client.id','=','client_authkey.ClientId')
            ->first(
                array('client.id')
            );
    
            $authorized = false;
    
            if($regKeyAuth && isset($regKeyAuth['id'])){
            
                $client = Client::where('id','=',$regKeyAuth['id'])->first();
                $user = $client->users->where('UserEmail','=',$request['UserEmail'])->first();
                return $userConfirmation = $user->userConfirmation;
    
                if($userConfirmation){
                    if($request['ConfirmationCode'] == $userConfirmation['UserConfirmationCode'] &&
                       $request['UserAppId'] == $userConfirmation['NewAppId']){
                        $user->update([
                            'UserAppId' => $userConfirmation['NewAppId'],
                            'UserConfirmationId' => null,
                        ]);
                        $userConfirmation->delete();
                        $authorized = true;
                       }
                }else{
                    return response()->
                    json(['result' => ['message'=>'Unable to confirm registration']], 500);
                }
            }
    
            if($authorized){
                $config = $this->getConfig($request);
                return $config;
            }else{
                return response()->
                json(['error' => 'Unauthorized'], 401);
            }
        } catch (Exception $e) {
            return response()->
            json($e, 500);
        }

    }

    /**
     * Gets application configuration.
     */
    public function getConfig(Request $request){
        $request->validate([
            'RegistrationKey' => 'required|string',
            'UserEmail' => 'required|string',
            'UserAppId' => 'required|string',
        ]);

        try {
            $regKeyAuth = ClientAuth::where('AuthKey','=',$request['RegistrationKey'])
                ->join('client', 'client.id','=','client_authkey.ClientId')
                ->first(
                    array('client.id')
                );

            // Must be accessible from the else closure.
            $user = null;

            $authorized = false;

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
                        'isAdmin' => $user['IsAdmin'],
                        'repositoryServer' => $clientServer['RepositoryServer'],
                        'registrationKey' => $registrationKey,
                        'token' => $this->generateRegToken($registrationKey),
                        'uuid' => $client['uuid'],  
                        'userEmail' => $user['UserEmail'], 
                        'userProfileId' => $user['UserProfileId'], 
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
                        $authorized = true;
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
                    $confirmationCode = $this->generateConfirmationCode();
                    $userConfirmation['UserConfirmationCode'] = $confirmationCode;
                    $userConfirmation->save();

                    // Updating the user profile with the new UserConfirmationId.
                    $user->update([
                        'UserConfirmationId' => $userConfirmation['UserConfirmationId']
                    ]);

                    // Send email to user with the confirmation code;
                    Mail::to($user->UserEmail)->send(new RegistrationConfirmation($confirmationCode));

                    return response()->
                    json(['result' => ['status'=>'Unauthorized','message'=>'Confirmation sent']], 200);
                }
            }

            if(!$authorized){
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
        return substr(str_shuffle("a0b1c2d3e4f5g6h7i8j9k@l!m%n0o1p2q3r4s5t6v7w8x9y@z"), 0, 8);
    }
}
