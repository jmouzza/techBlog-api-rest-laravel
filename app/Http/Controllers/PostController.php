<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Category;
use App\User;

class PostController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', [
            'except' => ['index', 'show', 'get_image', 'getPostsByCategory', 'getPostsByUser']
        ]);
    }

    private function loggedUserId(Request $request) {
        $token = $request->header('Authorization');
        $jwt = new \JwtAuth();
        $user = $jwt->checkToken($token, true);
        $user_id = $user['identity']->sub;
        return $user_id;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //Listar todos los POSTS
        $posts = Post::all()
                ->load('category')
                ->load('user');
        if (is_object($posts)) {
            $data_to_return = array(
                'status' => 'success',
                'code' => 200,
                'posts' => $posts
            );
        } else {
            $data_to_return = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se encontraron Posts en la base de datos'
            );
        }
        return response()->json($data_to_return, $data_to_return['code']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        //1.Recoger datos por post
        $json = $request->input('json', null);
        $post_array = json_decode($json, true);
        if (!empty($post_array)) {
            //2.Conseguir el id del usuario identificado
            $user_id = $this->loggedUserId($request);
            //3.Validar datos
            $validate = \Validator::make($post_array, [
                        'category_id' => 'required|numeric',
                        'title' => 'required|max:255',
                        'content' => 'required'
            ]);
            if ($validate->fails()) {
                $data_to_return = array(
                    'status' => 'error',
                    'code' => 400,
                    'error' => $validate->errors()
                );
            } else { //datos validados
                //4.Instanciar objeto y setear valores
                $post = new Post();
                $post->user_id = $user_id;
                $post->category_id = $post_array['category_id'];
                $post->title = $post_array['title'];
                $post->content = $post_array['content'];
                $post->image = $post_array['image'];
                // 5.Guardar nuevo POST (entrada)
                $post_saved = $post->save();
                if ($post_saved) {
                    $data_to_return = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Post guardado satisfactoriamente'
                    );
                } else {
                    $data_to_return = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Ocurrió un error al guardar su POST'
                    );
                }
            }
        } else {
            $data_to_return = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No se recibió JSON'
            );
        }

        //6.Devolver respuesta
        return response()->json($data_to_return, $data_to_return['code']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $post = Post::find($id)
                ->load('category')
                ->load('user');
        if (is_object($post) && !empty($post)) {
            $data_to_return = array(
                'status' => 'success',
                'code' => 200,
                'post' => $post
            );
        } else {
            $data_to_return = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Post no encontrado'
            );
        }
        return response()->json($data_to_return, $data_to_return['code']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {

        //1. Recoger json recibido por POST
        $json = $request->input('json', null);
        $post_to_update = json_decode($json, true); //convertirlo en array
        unset($post_to_update['category']);
        unset($post_to_update['user']);
        //2.Confirmar que se recibe el json
        if (!empty($post_to_update)) {
            //3.Recuperar el id del usuario identificado con el Token 
            $user_id = $this->loggedUserId($request);

            //4.Confirmar que el id del usuario identificado es el propietarios del post
            $post_info = Post::where('id', $id)->first();
            $post_user_id = $post_info->user_id;
            if ($user_id == $post_user_id) { //El usuario identificado es el propietario
                //5.Validar los datos recibidos en el json
                $validate = \Validator::make($post_to_update, [
                            'category_id' => 'required|numeric',
                            'title' => 'required|max:255',
                            'content' => 'required'
                ]);
                if ($validate->fails()) {
                    $data_to_return = array(
                        'status' => 'error',
                        'code' => 404,
                        'error' => $validate->errors()
                    );
                } else {
                    $post_to_update = array_map('trim', $post_to_update);
                    unset($post_to_update['user_id']);
                    unset($post_to_update['created_at']);
                    unset($post_to_update['updated_at']);
                    //Actualizar
                    $post_updated = Post::where('id', $id)->update($post_to_update);
                    if ($post_updated) {
                        $updated = Post::where('id', $id)->first();
                        $data_to_return = array(
                            'status' => 'success',
                            'code' => 200,
                            'post_updated' => $updated
                        );
                    } else {
                        $data_to_return = array(
                            'status' => 'error',
                            'code' => 400,
                            'message' => 'Ocurrió un error al actualizar el POST'
                        );
                    }
                }
            } else {
                $data_to_return = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No puedes actualizar debido a que no eres el propietario de este POST'
                );
            }
        } else {
            $data_to_return = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'No se recibió información suficiente para actualizar el POST'
            );
        }
        return response()->json($data_to_return, $data_to_return['code']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id) {

        //Conseguir el registro que será eliminado
        $post = Post::find($id);
        if ($post) {
            //1.Recuperar el id del usuario identificado con el Token 
            $user_id = $this->loggedUserId($request);

            //2.Confirmar que el id del usuario identificado es el propietarios del post
            $post_info = Post::where('id', $id)->first();
            $post_user_id = $post_info->user_id;
            if ($user_id == $post_user_id) { //El usuario identificado es el propietario
                $deleted = $post->delete();
                if ($deleted) {
                    $data_to_return = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Se ha eliminado el POST'
                    );
                } else {
                    $data_to_return = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Ocurrió un error al eliminar el POST'
                    );
                }
            } else {
                $data_to_return = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No puedes eliminar debido a que no eres el propietario de este POST'
                );
            }
        } else {
            $data_to_return = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El Post no existe'
            );
        }
        return response()->json($data_to_return, $data_to_return['code']);
    }

    public function upload_image(Request $request) {
        //1.Recoger la imagen de la petición
        $image = $request->file('file0');
        //2.Validar la imagen
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);
        if (!$image || $validate->fails()) {
            $data_to_return = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Ocurrió un error al subir la imagen'
            );
        } else {
            //3.Guardar la imagen en storage/images
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));

            $data_to_return = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'Imagen subida correctamente',
                'image' => $image_name
            );
        }
        //4.Devolver datos
        return response()->json($data_to_return, $data_to_return['code']);
    }

    public function get_image($filename) {
        //Comprobar que el archivo recibido por GET existe en el disk
        $file_isset = \Storage::disk('images')->exists($filename);
        if ($file_isset) {
            $file = \Storage::disk('images')->get($filename);
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

    public function getPostsByCategory($id) {
        //Comprobando que el id recibido pertenece a una categoria registrada
        $category_isset = Category::find($id);
        if ($category_isset) {
            $post = Post::where('category_id', $id)->get()
                        ->load('category')
                        ->load('user');

            if (count($post) > 0) {
                $data_to_return = array(
                    'status' => 'success',
                    'code' => 200,
                    'posts' => $post
                );
            } else {
                $data_to_return = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'La categoría solicitada no tiene POST asociado'
                );
            }
        } else {
            $data_to_return = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'La categoría no existe'
            );
        }
        return response()->json($data_to_return);
    }

    public function getPostsByUser($id) {
        //Comprobando que el id recibido pertenece a un usuario registrado
        $isset_user = User::find($id);
        if ($isset_user) {
            $posts = Post::where('user_id', $id)->get()
                            ->load('category')
                            ->load('user');
            
            if (count($posts) > 0) {
                $data_to_return = array(
                    'status' => 'success',
                    'code' => 200,
                    'posts' => $posts
                );
            } else {
                $data_to_return = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no ha publicado ningún POST'
                );
            }
        } else {
            $data_to_return = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no está registrado en la BBDD'
            );
        }
        return $data_to_return;
    }

}
