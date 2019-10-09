<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;

class PruebaController extends Controller {

    public function print_name($nombre = null) {
        $existe_nombre = false;
        if ($nombre != null) {
            $texto = $nombre;
            $existe_nombre = true;
        } else {
            $texto = "No ingresaste ningún nombre por parámetro";
        }
        return view('pruebas.prueba', array(
            'texto' => $texto,
            'existe_nombre' => $existe_nombre
        ));
    }

    public function animales() {
        $animales = ['Perro', 'Gato', 'Loro'];

        return view('pruebas.animales', [
            'animales' => $animales
        ]);
    }

    public function testOrm() {

        /* Mostrar los Posts oganizados por categoría */
        $categories = Category::all();
        foreach ($categories as $category) {
            echo "<h2>{$category->name}</h2>";
            echo "<ol>";
            foreach ($category->posts as $post) {
                echo "<li><p>{$post->user->name}</p><p>{$post->title} {$post->content}</p></li>";
            }
            echo "</ol>";
            echo "<hr/>";
        }
        die();
    }

}
