<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //Comprobar si el usuario está identificado correctamente
        $token = $request->header('Authorization');
        $jwt = new \JwtAuth();
        $checkToken = $jwt->checkToken($token);
        
        if($checkToken['auth'] == true){
            return $next($request);  
        }else{
            //Token incorrecto, usuario no identificado
            $data_to_return = array(
                'status'    => 'error',
                'code'      => 400,
                'message'   => 'El usuario no está identificado correctamente'
            );
            return response()->json($data_to_return, $data_to_return['code']);
        }
        
    }
}
