<?php

namespace App\Helpers;

use Firebase\JWT\JWT; //Librería JSON Web Token para utilizar en el método login de la app
use Illuminate\Support\Facades\DB; //Usar la librería que permite el uso e interacción con la base de datos con query builder
use App\User;

/*--------
 * Para usar este "helper/servicio" hay que cargarlo en Laravel, pasos: 
 * 1. consola-> make:provider JwtAuthServiceProvider
 * 2. En el método "register()" del provider creado, hacer un require_once del helper
 * 3. Cargar el provider en config/app en el apartado de 'providers' y 'aliases'  
 *    3.1. Namespace App\Providers\JwtAuthServiceProvider::class
 *    3.2. Asignar un alias para poder ser alcanzable en toda la APP 'JwtAuth' => App\Helpers\JwtAuth::class 
 -------- */

class JwtAuth{
    
    public $key;
    
    public function __construct() {
        $this->key = '0208--clave_secreta--0509';
    }
    
    public function signup($email, $password, $getToken = null){
        //1. Buscar el usuarios en la base de datos usando el ORM
        $user = User::where([
                    'email'     => $email,
                    'password'  => $password
                ])->first();
        //2. Comprobar si el email y la password son correctas (FLAG por defecto irá a false)
        $signup = false;
        if(is_object($user)){//Si la query devuelve un objeto las credenciales son correctas
            $signup = true;            
        }
        //3. Generar el token con JWT con los datos de usuario identificado correctamente
        
        /*--------
         * A JWT is simply a string but it contains three distinct parts separated with dots (.)
         * JTW = HEADER_HASH + '.' + PAYLOAD_HASH + '.' + SIGNATURE_HASH;
         * header: JSON string que contiende información del algoritmo que utilizará JWT para codificar.
         * payload: son los datos que queremos almacenar del usuario loggeado en el token (codificado){
         * signature: es un string encriptado que genera JWT junto con la key/clave secreta que le enviamos como parámetro
         --------*/
        
        if($signup){
            $token_payload = array(
                'sub'           => $user->id,
                'email'         => $user->email,
                'name'          => $user->name,
                'surname'       => $user->surname,
                'description'   => $user->description,
                'image'         => $user->image,
                'iat'           => time(),
                'exp'           => time() + (7 * 24 * 60 * 60)
            );
            /*----- 
             * Importante no asignar en payload datos sensibles como email/usuario o contraseña
             * propiedad 'iat' indicará cuando se ha creado el token
             * propiedad 'exp' indicará un tiempo de expiración/caducidad del token
             *      1 semana -> (7 * 24 * 60 * 60) 7 dias, 24 horas por día, 60 minutos por hora, 60 segundos por minuto
            -----*/
            
            $jwt = JWT::encode($token_payload, $this->key, 'HS256');
            
            //4. Devolver los datos; decodificados o token codificado. En función de un parámetro (flag)
            if(is_null($getToken)){
                $data_to_return = $jwt;//devolver token codificado
            }else{
                //decodificar el token para retornar los datos del payload
                $decoded = JWT::decode($jwt, $this->key, ['HS256']);
                $data_to_return = $decoded; //devolver token decodificado
            }
        }else{ // error de autenticación
            $data_to_return = array(
                'status'  => 'error',
                'message' => 'Login incorrecto'
            );
        }
        return $data_to_return;
    }
    
    public function checkToken($jwt, $getIdentity = false){
        $data_to_return = ['auth' => false];
        
        try{
            //Eliminar comillas del token, en caso de que llegue con comillas
            $jwt = str_replace('"','',$jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);//Comprobar si el token enviado devuelve al usuario (payload) decodificado en forma de objeto
        } catch (\UnexpectedValueException $e) {
            $data_to_return = ['auth' => false];
        } catch (\DomainException $e) {
            $data_to_return = ['auth' => false];
        }
        
        if(!empty($decoded) && is_object($decoded) && isset($decoded)){//El Helper devuelve un objeto, por lo tanto token enviado correcto 
            $data_to_return['auth'] = true;
            if($getIdentity){ //Cliente solicita los datos decodificados del usuario (payload)
                $data_to_return['identity'] = $decoded;
            }
        }else{
            $data_to_return = ['auth' => false];
        }
        return $data_to_return;
    }
    
}

