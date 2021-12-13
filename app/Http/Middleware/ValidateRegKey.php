<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ClientAuth;
use Carbon\Carbon;

class ValidateRegKey
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
            'RegistrationKey' => 'required',
        ]);

        $client = ClientAuth::where('AuthKey', '=', $request['RegistrationKey'])->first();

        if($client){

            // Checks if client subscription is expired.
            if($client['ExpirationDate'] < Carbon::now()){
                return response()->
                json(['error' => 'Expired Client'], 401);            }

            return $next($request);

        }else{
            return response()->
            json(['error' => 'Unauthorized'], 401);
        }

    }
}
