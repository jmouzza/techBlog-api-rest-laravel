<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    
    //Relación con category--- many-to-one
    public function category(){
        return $this->belongsTo('App\Category', 'category_id');   
    }
    
    //Relación con user--- many-to-one
    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
