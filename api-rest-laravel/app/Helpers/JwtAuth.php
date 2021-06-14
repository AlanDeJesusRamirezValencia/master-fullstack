<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\BD;
use App\User;

class JwtAuth {

    private $key;

    public function __construct() {
        $this->key = "qob%)=#$#_:_G?W=/EÑFOW!J#?3403rfñj8(/hfilueufhoa";
    }

    public function singup($email, $password, $getToken = null) {

        //Buscar si existe el usuario y sus credenciales
        $user = User::where([
            'email'     => $email,
            'password'  => $password
        ])->first();

        //Validar si las credenciales son correctas
        if(is_object($user)){

            //Generar el token con los datos del usuario identificado
            $token = array(
                'sub'       => $user->id,
                'email'     => $user->email,
                'name'      => $user->name,
                'surname'   => $user->surname,
                'iat'       => time(),
                'exp'       => time() + (7*24*60*60)
            );
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $jwtDecoded = JWT::decode($jwt, $this->key, ['HS256']);

            //Devolver los datos decodificados o el token, en función del parametro
            return ($getToken == null)? $jwt: $jwtDecoded;
        }

        //Validación fallida
        $data = array(
            'status'    => 'error',
            'code'      => 400,
            'message'   => 'login incorrecto'
        );
        return $data;
    }


    public function checkToken($jwt, $getIdentity = false) {
        $auth = false;
        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch(\UnexpectedValueException $e) {
            $auth = false;
        } catch(\DomainException $e) {
            $auth = false;
        }
        $auth = (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) ? true : false;
        return ($getIdentity) ? $decoded : $auth;
    }

}