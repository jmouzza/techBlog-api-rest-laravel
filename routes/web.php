<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* ----- Rutas para repaso ----- 
Route::get('/pruebas/nombre/{nombre?}', 'PruebaController@print_name');
Route::get('/pruebas/animales', 'PruebaController@animales');

/* ----- Rutas de pruebas ----- 
Route::get('/pruebas/testOrm', 'PruebaController@testOrm');
Route::get('/pruebas/post', 'PostController@pruebas');
Route::get('/pruebas/user', 'UserController@pruebas');
Route::get('/pruebas/category', 'CategoryController@pruebas');
*/

/*Métodos HTTP comunes para hacer uso de un API RESTful: (las API REST solo usan GET y POST)
GET: conseguir datos o recursos
POST: guardar datos, recursos o hacer una lógica programada (a través de un formulario)
PUT: Actualizar recursos o datos
DELETE: Eliminar datos o recursos
 * 
 * Importante saber que al ser utilizada como una API la protección por defecto de formularios CSRF
 * habrá que inhabilitarla (en el fichero Http/Kernel.php comentando el middleware de verificación csrf)  
 * 
*/

/* ----- Rutas del API ----- */
Route::get('/', function () {return view('welcome');});

//Rutas de UserController
Route::post('/api/register', 'UserController@register');
Route::post('/api/login', 'UserController@login');
Route::put('/api/update', 'UserController@update')->middleware('api.auth');
Route::post('/api/upload_avatar', 'UserController@upload_avatar')->middleware('api.auth');
Route::get('/api/get_avatar/{filename}', 'UserController@get_avatar');
Route::get('/api/detail/{id}', 'UserController@detail');

//Rutas de CategoryController
/*---------
 * Route::resources; con una sola línea de código se generarán las típicas rutas "CRUD" asignadas  
 * a un controlador, lo recomendable es que dicho controlador haya sido creado con el comando por consola
 * >php artisan make:controller CategoryController --resource
 * ya que se crearán de igual forma los métodos típicos 
 ---------*/
Route::resource('/api/category','CategoryController');

//Rutas de PostController
Route::resource('/api/post','PostController');
Route::post('/api/upload_image', 'PostController@upload_image')->middleware('api.auth');
Route::get('/api/get_image/{filename}', 'PostController@get_image');
Route::get('/api/post/category/{id}', 'PostController@getPostsByCategory');
Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');





 