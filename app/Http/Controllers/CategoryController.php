<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{

    /**
     * CategoryController constructor.
     *  The ApiAuthMiddleware is action in the CategoryController and it will authorize except exceptions
     * Ex. index/show
     */
    public function __construct()
    {
        $this->middleware('api.auth',['except' => ['index','show']]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * index method returns all categories from DB
     */
    public function index(Request $request)
    {
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'Success',
            'categories' => $categories
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * show method returns the category searched by id
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (is_object($category)){
            $data = [
                'code' => 200,
                'status' => 'Success',
                'category' => $category
            ];
        }else{
            $data = [
                'code' => 404,
                'status' => 'Error',
                'message' => 'This Category does not exists'
            ];
        }
        return response()->json($data, $data['code']);
    }


    public function store(Request $request)
    {
        // Recoger datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)){
            // Validar datos
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);

            // Guardar la categoria
            if ($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'Error',
                    'message' => 'The category does not save'
                ];
            }else{
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'Success',
                    'category' => $category
                ];
            }
        }else{
            $data = [
                'code' => 400,
                'status' => 'Error',
                'message' => 'You do not send any category'
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

        if (!empty($params_array)) {
            // Validar datos
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);
            // Quitar datos a no actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            // Actualizar el registro
            $category = Category::where('id', $id)->update($params_array);
            $data = [
                'code' => 200,
                'status' => 'Success',
                'category' => $params_array
            ];
        }else{
            $data = [
                'code' => 400,
                'status' => 'Error',
                'message' => 'You have not sent any category'
            ];
        }
        // Devolver datos
        return response()->json($data, $data['code']);
    }
}
