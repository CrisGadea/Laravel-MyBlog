<?php

namespace App\Http\Controllers;

use App\Post;
use App\Category;
use Illuminate\Http\Request;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth',['except' => ['index','show']]);
    }

    public function index(Request $request)
    {
        // Para cargar la categoría y que no la muestre como número
        $posts = Post::all()->load('category');

        return response()->json([
           'code' => 200,
           'status' => 'Success',
           'posts' => $posts
        ],200);
    }

    public function show($id)
    {
        $post = Post::find($id)->load('category');

        if (is_object($post)){
            $data = [
                'code' => 200,
                'status' => 'Success',
                'post' => $post
            ];
        }else{
            $data = [
                'code' => 404,
                'status' => 'Error',
                'message' => 'This Post does not exists'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {
        // Recoger datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = \GuzzleHttp\json_decode($json, true);

        if (!empty($params_array)){
            // Consrguir usuario identificado
            $jwtAuth = new JwtAuth();
            $token =$request->header('Authorization', null);
            $user = $jwtAuth->checkToken($token, true);

            // Validar datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);

            // Guardar post
            if ($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'Error',
                    'message' => 'The post does not save'
                ];
            }else{
                $post = new Post();
                $post->user_id = $user->sub;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->category_id = $params->category_id;
                $post->image = $params->image;
                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'Success',
                    'post' => $post
                ];
            }
        }else{
            $data = [
                'code' => 400,
                'status' => 'Error',
                'message' => 'You do not send any post'
            ];
        }

        // Devolver resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        // Recoger datos POST
        $json = $request->input('json', null);
        $params_array = json_decode($json , true);

        $data = [
            'code' => 400,
            'status' => 'Error',
            'message' => 'You have not sent any post'
        ];

        if (!empty($params_array)) {
            // Validar datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);
            if ($validate->fails()){
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }
            // Quitar datos a no actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            // Actualizar el registro
            $post = Post::where('id', $id)->update($params_array);
            $data = [
                'code' => 200,
                'status' => 'Success',
                'post' => $post,
                'changes' => $params_array
            ];
        }
        // Devolver datos
        return response()->json($data, $data['code']);
    }
}
