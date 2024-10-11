<?php

namespace App\Http\Controllers;

use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
use \stdClass;
//Clase para manejar las funciones de autenticacion
class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors());
        }

        // Asignamos el tipo de usuario por defecto
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'usuario'  // Tipo de usuario por defecto
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Asegúrate de devolver el campo user_type en la respuesta
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user_type' => $user->user_type // Incluye explícitamente el user_type
        ]);
    }



    public function login(Request $request){

        if (!Auth::attempt($request->only('email', 'password')))
        {
            return response()->json(['message' => 'Unauthorized'], 401);

        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(
            [
                'message' => 'Hola' , $user->name,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]
            );
    }

    public function logout(Request $request){
        $user = $request->user();

        $user->currentAccessToken()->delete();

        return response('',204);
    }
}
