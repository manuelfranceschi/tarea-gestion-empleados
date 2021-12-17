<?php

namespace App\Http\Controllers;

use App\Mail\password;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class userController extends Controller
{
    //1. Registro de empleados: solo accesible para usuarios directivos o de RRHH.
    public function registrar(Request $request){
        $jdata = $request->getContent(); //coge el string del JSON
        $data = json_decode($jdata);

        try {
            if ($data->name && $data->email && $data->password && $data->puesto && $data->salario && $data->biografia) {
                $user = new User();
                $user->name = $data->name;
                $user->email = $data->email;
                
                //password check
                if(preg_match("/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/", $data->password)){
                $user->password = Hash::make($data->password);
                }else {
                $response["msg"] = "Contraseña debil.";
                return response()->json($response);
                }
                $user->puesto = $data->puesto;
                $user->salario = $data->salario;
                $user->biografia = $data->biografia;
                $user->save();
                $response["msg"] = "Usuario guardado.";
                $response["user"] = $user;
            } else {
                $response["msg"] = "introduce todos los campos correctamente.";
            }
        } catch (\Throwable $th) {
            $response["msg"] = "introduce todos los campos correctamente.";
        }
        

        return response()->json($response);
        
    }
   
    //2. Login mediante email y contraseña.
    public function login(Request $request) {
        $jdata = $request->getContent(); //coge el string del JSON
        $data = json_decode($jdata);

        $user = User::where('email', $data->email)->first();
        
        try {
            if(Hash::check($data->password, $user->password)) {

                $user->api_token = Hash::make($user->email);
                $user->save();

                $response["msg"] = "Sesión iniciada.";
                $response["token"] = $user->api_token;

            }else{
                $response["msg"] = "Sesión incorrecta.";
            }
        } catch (\Throwable $th) {
            $response["msg"] = "introduce correctamente tu contraseña.";
        }
        return response()->json($response);
    }

    //3. Recuperar contraseña, introduciendo el email. Genera una nueva contraseña aleatoria y envía un email.
    public function recuperarPassword(Request $request) {
        $jdata = $request->getContent(); //coge el string del JSON
        $data = json_decode($jdata);

        $user = User::where('email', $data->email)->first();

        if($user){
            $password = Str::random(15);
            $user->password = Hash::make($password);
            $user->save();
            $response['msg'] = 'contraseña enviada'; 
            $response['contraseña'] = $password ;
            Mail::to($user->email)->send(new password($password));
        }

        return response()->json($response);
    }

    /*4. Lista de empleados: solo accesible para directivos y RRHH. 
    Muestra Nombres, puestos de trabajo y salarios de los empleados normales. 
    Si es directivo, también muestra los datos de los empleados de RRHH. */
    public function lista(Request $request){
        $jdata = $request->getContent(); //coge el string del JSON
        $data = json_decode($jdata);

        $user = User::where('api_token', $data->api_token)->first();
        
        //Verifica que el usuario que crea otros nuevos usuarios sea directivo o rrhh
        
        $empleados= User::where('puesto','empleado')->get();
            
        $id = 1;
        foreach ($empleados as $key => $empleado) {
            $response[$id]['id'] = $empleado->id;
            $response[$id]['name'] = $empleado->name;
            $response[$id]['puesto'] = $empleado->puesto;
            $response[$id]['salario'] = $empleado->salario;
            $id++;
        }

        if($user->puesto == "directivo"){
            $RRHH= User::where('puesto','RRHH')->get();

            foreach ($RRHH as $key => $empleado) {
                $response[$id]['id'] = $empleado->id;
                $response[$id]['name'] = $empleado->name;
                $response[$id]['puesto'] = $empleado->puesto;
                $response[$id]['salario'] = $empleado->salario;
                $id++;
            }
        }
        return response()->json($response);
    }

    /*5. Detalle del empleado: solo accesible para directivos y RRHH. 
    Muestra nombre, email, puesto de trabajo, biografía y salario de un empleado normal. 
    Si el usuario es directivo, también puede consultar esta información de los usuarios de RRHH.*/
    public function detalle (Request $request){
        $jdata = $request->getContent(); //coge el string del JSON
        $data = json_decode($jdata);
        
        $user = User::where('api_token', $data->api_token)->first();
        $empleado = User::where('id', $data->id)->first();
        //asignar rango del buscador
        if($user->puesto == 'RRHH') $rangoUser = 2;
        if($user->puesto == 'directivo') $rangoUser = 3;
        //asignar rango del empleado
        if($empleado->puesto == 'directivo') $rangoEmpleado = 3;
        if($empleado->puesto == 'RRHH') $rangoEmpleado = 2;
        if($empleado->puesto == 'empleado') $rangoEmpleado = 1;
        //mandar detalles del empleado por id
        if($user->id == $empleado->id || $rangoUser > $rangoEmpleado) {
            $response['id'] = $empleado->id;
            $response['name'] = $empleado->name;
            $response['email'] = $empleado->email;
            $response['puesto'] = $empleado->puesto;
            $response['biografia'] = $empleado->biografia;
            $response['salario'] = $empleado->salario;
            
        } else {
            $response['msg'] = 'no tienes permisos';
        }
        return response()->json($response);
    }

    //6. Ver perfil: accesible a cualquier usuario logeado. Muestra los datos completos del usuario que ha iniciado sesión.
    public function perfil (Request $request) {
        $jdata = $request->getContent(); //coge el string del JSON
        $data = json_decode($jdata); //datos que le pasamos desde el postman
        
        $user = User::where('api_token', $data->api_token)->first();
        
        if ($user) {
            $response['id'] = $user->id;
            $response['name'] = $user->name;
            $response['email'] = $user->email;
            $response['puesto'] = $user->puesto;
            $response['biografia'] = $user->biografia;
            $response['salario'] = $user->salario;
        } else{
            $response['msg'] = 'no has iniciado sesion';
        }
        return response()->json($response);
    }
    /*7.  Modificar datos de empleado: solo accesible para directivos y RRHH. 
          Permite modificar los datos de un empleado normal indicando los nuevos datos. 
          Si es un usuario directivo, también puede modificar los datos de los usuarios de RRHH y los suyos propios, 
          pero no los de otro directivo. */

    public function modificar(Request $request) {
        $jdata = $request->getContent(); //coge el string del JSON
        $data = json_decode($jdata); //datos que le pasamos desde el postman

        $user = User::where('api_token', $data->api_token)->first();

        $empleado = User::where('id', $data->id)->first();
        
        //asignar rango del buscador

        if($user->puesto == 'RRHH') $rangoUser = 2;
        if($user->puesto == 'directivo') $rangoUser = 3;

        //asignar rango del empleado

        if($empleado->puesto == 'directivo') $rangoEmpleado = 3;
        if($empleado->puesto == 'RRHH') $rangoEmpleado = 2;
        if($empleado->puesto == 'empleado') $rangoEmpleado = 1;

        //mmodificar datos del empleado por id
        
        if(($user->id == $empleado->id && $user->puesto == 'directivo')  || $rangoUser > $rangoEmpleado) {
           $empleado->name = $data->name;
           $empleado->email = $data->email;
           //password check
           if(preg_match("/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/", $data->password)){
            $empleado->password = Hash::make($data->password);
        }else {
            $response["msg"] = "Contraseña debil.";
            return response()->json($response);
        }
           $empleado->puesto = $data->puesto;
           $empleado->salario = $data->salario;
           $empleado->biografia = $data->biografia;
           $empleado->save();
           $response["msg"] = "Usuario modificado.";
           $response["empleado"] = $empleado;
        }  else if ($rangoUser == $rangoEmpleado){
            $response['msg'] = 'un directivo no puede cambiar los datos de otro directivo';
        }else {
            $response['msg'] = 'no tienes permisos';
        }
        return response()->json($response);
    }
    }




