<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller {

    public function pruebas(Request $request) {
        return "Acción pruebas UserController";
    }

    public function register(Request $request) {

        //1. Recibir los datos enviados por post a través de un json string
        $json = $request->input('json', null); //si recibe clave 'json' tomarlo, sino asignar valor null
        //2. Decodificar 'json' 2 opciones: objeto o array
        $user_object = json_decode($json); //decodificación a objeto php
        $user_array = json_decode($json, true); //decodificación array
        //3. Verificar que no este vacío el objeto o el array
        if (!empty($user_object) && !empty($user_array)) {

            //4. Limpiar los datos(trim) del array para evitar espacios en blanco
            $user_array = array_map('trim', $user_array);
            //5. Validar los datos recibidos
            $validate = \Validator::make($user_array, [
                        'name' => 'required|alpha_spaces',
                        'surname' => 'required|alpha_spaces',
                        'email' => 'required|email|unique:users', //Comprobar si el email existe (duplicado) en la base de datos
                        'password' => 'required'
            ]);
            if ($validate->fails()) { //5.1. Si hay algun error de validación
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors() // Se asignará al indice errors el error de validación
                );
            } else { //5.2. Datos validados correctamente, continuar
                $name = $user_array['name'];
                $surname = $user_array['surname'];
                $email = $user_array['email'];
                $password = $user_array['password'];
                //6. Cifrar contraseña
                $password_cifrada = hash('sha256', $password);

                //7. Crear el objeto (usuario) setear valores validados
                $user = new User();
                $user->name = $name;
                $user->surname = $surname;
                $user->email = $email;
                $user->password = $password_cifrada;
                $user->role = 'ROLE_USER';

                //8. Guardar objeto (usuario)
                $user_saved = $user->save();

                if ($user_saved) {
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Usuario creado correctamente',
                        'user' => $user
                    );
                }
            }
        } else { //en caso que el json venga vacío o tenga algun error de sintaxis que no permita interpretarlo
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos enviados no son correctos'
            );
        }
        //9. Devolver una respuesta convertida en formato JSON al front end
        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {

        //1. Recibir los datos enviados por post a través de un json string
        $json_user = $request->input('json', null);
        //2. Convertir json string en objeto php o en array
        $data_login_object = json_decode($json_user);

        $data_login_array = json_decode($json_user, true);

        //3. Validar datos principalmente para indicar que son obligatorios
        $validate = \Validator::make($data_login_array, [
                    'email' => 'required|email',
                    'password' => 'required'
        ]);
        if ($validate->fails()) { //3.1. Si hay algun error de validación
            $data_to_return = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Error de Login',
                'errors' => $validate->errors() // Se asignará al indice 'errors' el error de validación
            );
        } else {//3.2. Si no hay fallo de validación, continuar...
            //4. Cifrar la contraseña
            $password_cifrada = hash('sha256', $data_login_object->password);
            //5. Devolver Token utilizando la librería JWT  y el helper creado
            $jwt = new \JwtAuth();
            $token = $jwt->signup($data_login_object->email, $password_cifrada); //Solo devolverá el token cifrado

            if (isset($token['status']) && $token['status'] == 'error') {
                $data_to_return = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Datos incorrectos'
                );
            } else {
                $data_to_return = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Sesión iniciada',
                    'encoded_token' => $token
                );
                if (isset($data_login_object->getToken) && ($data_login_object->getToken === true || $data_login_object->getToken == "true")) {
                    $decoded = $jwt->signup($data_login_object->email, $password_cifrada, true); //decodificará el token y devolverá los datos del usuario(payload)
                    $data_to_return = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Sesión iniciada',
                        'encoded_token' => $token,
                        'decoded_token' => $decoded
                    );
                }
            }
        }
        return response()->json($data_to_return, $data_to_return['code']);
    }

    public function update(Request $request) {
        /* --- Método protegido por el middleware "ApiAuthMiddleware" comprobación de token --- */

        /* ---Pasos para actualizar usuario: --- */

        //1.Recoger datos enviados en la petición HTTP - PUT 
        $json = $request->input('json', null); //llegan en formato string

        $update_user_array = json_decode($json, true);

        if (!empty($json)) {//si se recibe JSON 
            //2. Recoger los datos del usuario (payload) que trae el token antes de ser actualizado
            $jwt = new \JwtAuth();
            $token = $request->header('Authorization');
            $user = $jwt->checkToken($token, true);
            $user_id = $user['identity']->sub;

            //3. Validar datos 
            $validate = \Validator::make($update_user_array, [
                        'name' => 'required|alpha_spaces',
                        'surname' => 'required|alpha_spaces',
                        'email' => 'required|email|unique:users,email,'. $user_id
            ]);
            if ($validate->fails()) {
                $data_to_return = array(
                    'status' => 'error',
                    'code' => 400,
                    'errors' => $validate->errors()
                );
            } else {
                //4. Asegurarse de no actualizar campos sensibles (en caso que lleguen por el json de manera maliciosa) 
                unset($update_user_array['id']);
                unset($update_user_array['role']);
                unset($update_user_array['password']);
                unset($update_user_array['created_at']);
                unset($update_user_array['remember_token']);
                
                //5. Actualizar datos en BBDD
                $user_update = User::where('id', $user_id)->update($update_user_array);
                //6. Devolver Array con resultado
                if ($user_update) {//si se actualiza el usuario
                    $updated = User::where([
                                'id' => $user_id
                            ])->first();
                    $data_to_return = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Usuario actualizado',
                        'user' => $updated
                    );
                } else {
                    $data_to_return = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Ocurrió un error al actualizar los datos'
                    );
                }
            }
        } else {//Api no ha recibido un json
            $data_to_return = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Datos insuficientes para realizar actualización de datos'
            );
        }
        return response()->json($data_to_return, $data_to_return['code']);
    }

    public function upload_avatar(Request $request) {
        /* --- Método protegido por el middleware "ApiAuthMiddleware" comprobación de token --- */
        /* --- Pasos para guardar una imagen: --- */

        //1. Recoger los datos de la petición
        $avatar = $request->file('file0');
        //2. Validación de imagen en formato correcto
        $avatar_validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);
        if (empty($avatar) || $avatar_validate->fails()) {//si no llega información por post o existe algun fallo
            $data_to_return = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Error al subir imagen',
                'errors' => $avatar_validate->errors()
            );
        } else {//No hay errores
            //3. Guardar imagen en disco virtual (carpeta dentro del storage)
            $avatar_name = time() . $avatar->getClientOriginalName();
            \Storage::disk('users')->put($avatar_name, \File::get($avatar));
            //4. Crear la respuesta positiva
            $data_to_return = array(
                'status' => 'success',
                'code' => 200,
                'image' => $avatar_name,
                'message' => 'El avatar ha sido actualizado correctamente'
            );
        }
        //5. Devolver resultado (success o error)
        return response()->json($data_to_return, $data_to_return['code']);
    }

    public function get_avatar($filename) {
        //Comprobar que el archivo recibido por GET existe en el disk
        $file_isset = \Storage::disk('users')->exists($filename);
        if ($file_isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {//archivo no encontrado
            $data_to_return = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'La imagen no existe'
            );
            return response()->json($data_to_return, $data_to_return['code']);
        }
    }

    public function detail($id) {
        $user_detail = User::find($id);

        if (is_object($user_detail)) {
            $data_to_return = array(
                'status' => 'success',
                'code' => 200,
                'user' => $user_detail
            );
        } else {
            $data_to_return = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Usuario no encontrado'
            );
        }

        return response()->json($data_to_return, $data_to_return['code']);
    }

}
