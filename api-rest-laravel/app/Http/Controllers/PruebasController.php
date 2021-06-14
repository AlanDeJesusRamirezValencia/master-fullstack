<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;

class PruebasController extends Controller {
    public function index() {

    }

    public function testORM() {
        $posts = Post::all();
        foreach($posts as $post){
             echo "<h1>".$post->title."</h1>";
             echo "<h3>".$post->content."</h3>";
        }
        die();
    }
}
