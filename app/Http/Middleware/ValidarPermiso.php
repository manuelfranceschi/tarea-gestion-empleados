<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ValidarPermiso
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

        $jdata = $request->getContent(); //coge el string del JSON
        $data = json_decode($jdata);

        $user = User::where('api_token', $data->api_token)->first();
       
        //Verifica que el usuario que crea otros nuevos usuarios sea directivo o rrhh
        if ($user->puesto == 'RRHH' || $user->puesto == 'directivo' ) {
            return $next($request);
        } else {
            $response['msg'] = "no tienes permisos";
            return response()->json($response);
        }
        

        
        
    }
}
