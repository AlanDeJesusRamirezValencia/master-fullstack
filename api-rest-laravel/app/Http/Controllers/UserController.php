<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller {

    private function incorrectData() {
        $data = array(
            'status'    => 'error',
            'code'      => 404,
            'message'   => 'los datos enviados no son correctos',
        );
        return response()->json($data, $data['code']);
    }

    public function register(Request $request) {

        // Recoger los datos del usuario
        $json           = $request->input('json', null);
        $params         = json_decode($json);
        $params_array   = json_decode($json, true);

        // Validar los datos
        if(empty($params)|| empty($params_array)){
            return incorrectData();
        }

        $params_array = array_map('trim', $params_array);
        
        // Comprobar si el usuario ya existe (duplicado)
        $validate = \Validator::make($params_array, [
            'name'      => 'required|alpha',
            'surname'   => 'required|alpha',
            'email'     => 'required|email|unique:users',
            'password'  => 'required'
        ]);

        if($validate->fails()) {    
            //Validación fallida
            $data = array(
                'status'    =>'error',
                'code'      => 404,
                'message'   => 'el usuario no se ha creado',
                'errors'    => $validate->errors()
            );
            return response()->json($data, $data['code']);
        }

        // Validación pasada correctamente
        // Cifrar la contraseña
        $pwd = hash('sha256', $params->password);
        
        // Crear el usuario
        $user = new User();
        $user->name     = $params_array['name'];
        $user->surname  = $params_array['surname'];
        $user->email    = $params_array['email'];
        $user->role     = 'ROLE_USER';
        $user->password = $pwd;

        //Guardar el usuario
        $user->save();

        //Mostrar mensaje de éxito
        $data = array(
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'el usuario se ha creado correctamente',
            'user'      => $user
        );

        return response()->json($data, $data['code']);
    } //Fin del método register

    
    public function login(Request $request) {
        
        // Recoger los datos del usuario
        $json           = $request->input('json', null);
        $params         = json_decode($json);
        $params_array   = json_decode($json, true);

        // Validar los datos
        if(empty($params)|| empty($params_array)){
            return incorrectData();
        }

        $params_array = array_map('trim', $params_array);
        // Comprobar si el usuario ya existe (duplicado)
        $validate = \Validator::make($params_array, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        if($validate->fails()) {
            // Validación fallida
            $data = array(
                'status'    =>'error',
                'code'      => 404,
                'message'   => 'el usuario no identificado',
                'errors'    => $validate->errors()
            );
            return response()->json($data, $data['code']);
        }

        // Validación pasada correctamente
        // Cifrar la contraseña
        $pwd = hash('sha256', $params->password);

        // Devolver token o datos
        $jwtAuth = new \JwtAuth();
        $singup = (!empty($params->gettoken))
            ? $jwtAuth->singup($params->email, $pwd, true)
            : $jwtAuth->singup($params->email, $pwd);
            
        return response()->json($singup, 200);
    } // Fin del método login


    public function update(Request $request) {

        //Comprobar si el usuario está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        //Recoger los datos por POST
        $json           = $request->input('json', null);
        $params_array   = json_decode($json, true);
        
        //Validar si el token es correcto
        if( ! ($jwtAuth->checkToken($token)) || empty($params_array)) {  
            return incorrectData();
        }

        //Sacar el usuario identificado
        $user = $jwtAuth->checkToken($token, true);

        // Validar los datos
        $validate = \Validator::make($params_array, [
            'name'      => 'required|alpha',
            'surname'   => 'required|alpha',
            'email'     => 'required|email|unique:users, '.$user->sub
        ]);
        //Quitar los datos que no quiero actualizar
        unset($params_array['id']);
        unset($params_array['role']);
        unset($params_array['password']);
        unset($params_array['created_at']);
        unset($params_array['remember_token']);

        //Actualizar usuario en la base de datos
        $user_update = User::where('id', $user->id)->update($params_array);

        //Devolver array con resultado
        $data = array(
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'el usuario se ha creado correctamente',
            'user'      => $user
        );
        return response()->json($data, $data['code']);
    }
}