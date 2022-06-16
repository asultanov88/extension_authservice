<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ClientAuth;

class AutheticateClientUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $request->validate([
            'RegistrationKey' => 'required|string',
            'UserEmail' => 'required|string',
            'UserAppId' => 'required|string',
        ]);

        $regKeyAuth = ClientAuth::where('AuthKey','=',$request['RegistrationKey'])
            ->join('client','client.id','=','client_authkey.ClientId')
            ->join('client_user_profiles','client_user_profiles.ClientId','=','client.id')
            ->where([
                        ['client_user_profiles.UserEmail','=',$request['UserEmail']],
                        ['client_user_profiles.UserAppId','=',$request['UserAppId']]
                    ])
            ->first(
                array(
                    'client.id AS ClientId',
                    'client_user_profiles.IsAdmin',
                    )
            );

        if($regKeyAuth && isset($regKeyAuth['ClientId'])){
            $request['ClientId'] = $regKeyAuth['ClientId'];
            $request['AdminUser'] = $regKeyAuth['IsAdmin'] == 1 ? true : false;
            return $next($request);
        }else{
            return response()->
            json(['error' => 'Unauthorized'], 401);
        }
    }
}
