<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class userController extends Controller
{
    public function registrar(Request $request){
        $jdata = $request->getContent(); //coge el string del JSON
        $data = json_decode($jdata);

        if ($data->name && $data->email && $data->password && $data->puesto && $data->salario && $data->biografia) {
            $user = new User();
            $user->name = $data->name;
            $user->email = $data->email;
            $user->password = Hash::make($data->password);
            $user->puesto = $data->puesto;
            $user->salario = $data->salario;
            $user->biografia = $data->biografia;
            $user->save();
            $response["msg"] = "Usuario guardado.";
            $response["user"] = $user;
        } else {
            $response["msg"] = "introduce todos los campos correctamente.";
        }
        
        return response()->json($response);
        
    }
}
