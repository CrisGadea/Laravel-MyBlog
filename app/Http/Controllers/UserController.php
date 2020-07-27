<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $usuario = ['Nombre' => 'Cristian','Apellido' => 'Gadea', 'Edad' => 23];
        return view('user',[
            'usuario' => $usuario
        ]);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function register(Request $request)
    {
        // Reccoger datos del usuario por post, por defecto null
        $json = $request->input('json',null);

        //Obtengo los datos del json como objeto
        $params = json_decode($json);
        //Obtengo los datos del json como array
        $params_array = json_decode($json, true);

        /*var_dump($params->username);
        var_dump($params_array);die();*/

        // Solo seguir si no esta vacio el params
        if (!empty($params_array)) {

            //Limpiar datos
            $params_array = array_map('trim', $params_array);

            // Validar datos
            $validate = \Validator::make($params_array, [
                'username' => 'required|alpha_dash',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);

            if ($validate->fails()) {
                // La validación falló
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'messagge' => 'The user does not has been created',
                    'errors' => $validate->errors()
                ];
            } else {
                // Cifrar la contraseña
                $pwd = hash('sha256', $params->password);

                // Crear el usuario
                $user = new User();
                $user->username = $params_array['username'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->description = $params_array['description'];
                $user->role = 'ROLE_USER';

                // Guardar el usuario
                $user->save();

                $data = [
                    'status' => 'Created',
                    'code' => 201,
                    'messagge' => 'The user has been created successfully',
                    'user' => $user
                ];
            }

        }else{
            $data = [
                'status' => 'error',
                'code' => 400,
                'messagge' => 'The data was not correctly sent'
            ];
        }


        return response()->json($data, $data['code']);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function login(Request $request)
    {
        $jwtAuth = new JwtAuth();

        // Recibir datos por POST
        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        // Validar datos
        $validate = \Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validate->fails()) {
            // La validación falló
            $signup = [
                'status' => 'error',
                'code' => 400,
                'messagge' => 'The user does not has been identified',
                'errors' => $validate->errors()
            ];
        } else {
            // Cifrar contraseña
            $pwd = hash('sha256',$params->password);
            //Devolver Token o datos
            $signup = $jwtAuth->signup($params->email,$pwd);
            if(isset($params->getToken)){
                // Devuelve datos decodificados
                $signup = $jwtAuth->signup($params->email,$pwd, true);
            }
        }

        return response()->json($signup, 200);
    }

    public function update(Request $request)
    {
        // Comprobar si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth();
        $checktoken = $jwtAuth->checkToken($token);

        // Recoger datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json,true);

        if ($checktoken && !empty($params_array)) {
            // Sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            // Validar datos
            $validate = \Validator::make($params_array, [
                'username' => 'required|alpha_dash',
                'email' => 'required|email|unique:users,'.$user->sub
            ]);
            // Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            // Actualizar Usuario
            $user_update = User::where('id',$user->sub)->update($params_array);

            // Devolver array con resultado
            $data = [
                'code' => 200,
                'status' => 'Success',
                'user' => $user,
                'changes' => $params_array
            ];

        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'User was not identified'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {
        // Recoger datos
        $image = $request->file('file0');

        // Validacion de la imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Subir y guardar imagen
        if (!$image || $validate->fails()){
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Fails try to upload an image'
            ];
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'Success',
                'image' => $image_name
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
        $isset = \Storage::disk('users')->exists($filename);

        if ($isset){
            $file = \Storage::disk('users')->get($filename);

            return new Response($file, 200);
        }else{
            $data = [
                'code' => 404,
                'status' => 'Error',
                'message' => 'The image does not exists'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function profile($id)
    {
        $user = User::find($id);

        if (is_object($user)){
            $data = [
                'code' => 200,
                'status' => 'Success',
                'user' => $user
            ];
        }else{
            $data = [
                'code' => 404,
                'status' => 'Error',
                'messagge' => 'The user does not exists'
            ];
        }

        return response()->json($data, $data['code']);
    }
}
