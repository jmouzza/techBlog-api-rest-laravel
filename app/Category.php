<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    
    //Relación con posts one-to-many
    public function posts(){
        return $this->hasMany('App\Post');
    }
}
