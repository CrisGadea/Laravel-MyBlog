<?php
namespace App\Helpers;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth
{
    public $key;

    public function __construct()
    {
        $this->key = "esto_es_una_clave_secreta_-58484658";
    }

    public function signup($email, $password, $getToken = null)
    {
        // Buscar si existe el usuario con sus credenciales
         $user = User::where([
             'email' => $email,
             'password' => $password
         ])->first();

        // Comprobar si son correctas (Objeto)
        $signup = false;
        if (is_object($user)){
            $signup = true;
        }

        // Generar el Token con los datos del usuario identificado
        if ($signup){
            $token = [
                'sub' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            ];

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, ['HS256']);
            // Devolver los datos decodificados o token por parametro
            if (is_null($getToken)){
                $data = $jwt;
            }else{
                $data = $decode;
            }
        }else{
            $data = [
                'status' => 'error',
                'message' => 'Invalid login.'
            ];
        }

        return $data;
    }

}
