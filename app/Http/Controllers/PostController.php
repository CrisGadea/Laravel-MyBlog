<?php

namespace App\Http\Controllers;

use App\Post;
use App\Category;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        return "Test from PostController";
    }

    public function testOrm()
    {
        $posts = Post::all();
        var_dump($posts);

        /*foreach ($posts as $post){
            echo "<h1>".$post->title."</h1>";
            echo "<span>{$post->user->name}-{$post->category->name}</span>";
        }*/
        die();
    }
}
