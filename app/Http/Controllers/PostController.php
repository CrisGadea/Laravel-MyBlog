<?php

namespace App\Http\Controllers;

use App\Post;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\JwtAuth;

/**
 * Class PostController
 * @package App\Http\Controllers
 * Controlador Usuario
 */
class PostController extends Controller
{
    /**
     * PostController constructor.
     * Recibe el AuthMiddleware mediante inyección de dependencias para autorización del usuario
     */
    public function __construct()
    {
        $this->middleware('api.auth',['except' => [
            'index',
            'show',
            'getImage',
            'getPostsByCategory',
            'getPostsByUser'
        ]]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Devuelve el contenido de todos los posts creados
     */
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

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * Devuelve el post buscado
     */
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Guarda un post nuevo
     */
    public function store(Request $request)
    {
        // Recoger datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = \GuzzleHttp\json_decode($json, true);

        if (!empty($params_array)){
            // Conseguir usuario identificado
            $user = $this->getIdentity($request);

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

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Actualiza el post elegido
     */
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

            // Conseguir usuario identificado
            $user = $this->getIdentity($request);

            // Buscar el registro
            $post = Post::where('id', $id)
                        ->where('user_id', $user->sub)
                        ->first();

            if (!empty($post) && is_object($post)){
                // Actualizar registro
                $post->update($params_array);

                // Devolver data
                $data = [
                    'code' => 200,
                    'status' => 'Success',
                    'post' => $post,
                    'changes' => $params_array
                ];
            }

            /*// Actualizar el registro
            $where = [
                'id' => $id,
                'user_id' => $user->sub
            ];
            $post = Post::updateOrCreate($where, $params_array);
            $data = [
                'code' => 200,
                'status' => 'Success',
                'post' => $post,
                'changes' => $params_array
            ];*/
        }
        // Devolver datos
        return response()->json($data, $data['code']);
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * Elimina el post elegido por un usuario logueado.
     * Si el usuario no creó el post, devuelve error
     */
    public function destroy($id, Request $request)
    {
        // Conseguir usuario identificado
        $user = $this->getIdentity($request);

        // Conseguir el post
        $post = Post::where('id',$id)
                    ->where('user_id', $user->sub)
                    ->first();

        if (!empty($post)){
            // Borrar el registro
            $post->delete();
            // Devolver data
            $data = [
                'code'=> 200,
                'status' => 'Success',
                'post' => $post
            ];
        }else{
            $data = [
                'code'=> 400,
                'status' => 'Error',
                'mesagge' => 'The post does not exists'
            ];
        }

        return response()->json($data, $data['code']);
    }

    /**
     * @param Request $request
     * @return bool|object
     * Obtener el usuario logueado
     */
    private function getIdentity(Request $request)
    {
        $jwtAuth = new JwtAuth();
        $token =$request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload(Request $request)
    {
        // Recoger imagen de la peticion
        $image = $request->file('file0');

        // Validar la imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar imagen en disco
        if (!$image || $validate->fails()){
            $data = [
                'code' => 400,
                'status' => 'Error',
                'mesagge' => 'Fails to try to upload the image'
            ];
        }else{
            $image_name = time().$image->getClientOriginalName();

            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'Success',
                'image' => $image_name
            ];
        }

        // Devolver datos
        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
        // Comprobar si existe fichero
        $isset = \Storage::disk('images')->exists($filename);

        $data = [
            'code' => 404,
            'status' => 'Error',
            'mesagge' => 'Fails to try to find the image'
        ];

        if ($isset){
            // Conseguir la imagen
            $file = \Storage::disk('images')->get($filename);
            // Devolver imagen
            return new Response($file, 200);
        }

        // Mostrar error
        return repsonse()->json($data, $data['code']);
    }

    public function getPostsByCategory($id)
    {
        $posts = Post::where('category_id', $id)->get();

        return response()->json([
            'status' => 'Success',
            'posts' => $posts
        ], 200);
    }

    public function getPostsByUser($id)
    {
        $posts = Post::where('user_id', $id)->get();

        return response()->json([
            'status' => 'Success',
            'posts' => $posts
        ], 200);
    }
}
