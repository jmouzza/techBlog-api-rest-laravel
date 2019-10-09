<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\Category;

class CategoryController extends Controller {

    public function __construct() {
        //Cargar middleware de autenticación con token en todos los métodos excepto los métodos indicados
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index() {
        //Listar categorias existentes en la base de datos
        $categories = Category::all();
        return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'categories' => $categories
        ]);
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
        //Método para guardar una categoría nueva en la BBDD. Pasos:
        //1. Recibir json string con el nombre de la categoría
        $json = $request->input('json', null);
        //2. Convertir json string -> array y luego asignar a una variable el nombre de la categoria
        $category_name = json_decode($json, true); //convertido en array
        //3. Limpiar y validar dato recibido por POST
        if (!empty($category_name)) {
            $category_name = array_map('trim', $category_name);
            $validate = \Validator::make($category_name, [
                        'name' => 'required|alpha_num_spaces|unique:categories'
            ]);
            if ($validate->fails()) {
                $data_to_return = array(
                    'status' => 'error',
                    'code' => 400,
                    'error' => $validate->errors()
                );
            } else {// Nombre de categoría validada
                //4. Guardar categoría en BBDD
                $new_category = new Category();
                $new_category->name = $category_name['name'];
                //5. Guardar objeto (categoría)
                $category_saved = $new_category->save();

                if ($category_saved) {
                    $data_to_return = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Categoría creada correctamente',
                        'category' => $new_category
                    );
                } else {
                    $data_to_return = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Ocurrió un error al crear la categoría'
                    );
                }
            }
        } else {
            $data_to_return = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Nombre de categoría no recibida'
            );
        }
        return response()->json($data_to_return, $data_to_return['code']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //Método que mostrará categoría única según el id recibido como parámetro
        $category = Category::find($id);

        if (is_object($category)) {
            $data_to_return = array(
                'status' => 'success',
                'code' => 200,
                'category' => $category
            );
        } else {
            $data_to_return = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Categoría no encontrada'
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
        //Método para actualizar una categoría en la BBDD. Pasos:
        //1. Recibir json string con el nombre de la categoría
        $json = $request->input('json', null);
        //2. Convertir json string -> array
        $category_name_update = json_decode($json, true); //convertido en array
        //3. Limpiar y validar dato recibido por POST
        if (!empty($category_name_update)) {
            $category_name_update = array_map('trim', $category_name_update);
            $validate = \Validator::make($category_name_update, [
                        'name' => 'required|alpha|unique:categories'
            ]);
            if ($validate->fails()) {
                $data_to_return = array(
                    'status' => 'error',
                    'code' => 400,
                    'error' => $validate->errors()
                );
            } else {// Nombre de categoría validada
                //4. Asegurarse de no actualizar campos sensibles (en caso que lleguen por el json de manera maliciosa) 
                unset($category_name_update['created_at']);
                unset($category_name_update['updated_at']);
                //5. Actualizar datos en BBDD
                $category_updated = Category::where('id', $id)->update($category_name_update);
                if($category_updated){
                    //6. Devolver Array con resultado
                    $updated = Category::where([
                                'id' => $id
                            ])->first();
                    $data_to_return = array (
                        'status'            => 'success',
                        'code'              => 200,
                        'message'           => 'La categoría fue actualizada correctamente',
                        'category_updated'  => $updated
                    );
                }else{
                    $data_to_return = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Ocurrió un error al guardar la categoría'
                    );
                }
                
            }
        } else {
            $data_to_return = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Nombre de categoría no recibida'
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
    public function destroy($id) {
        //
    }

}
