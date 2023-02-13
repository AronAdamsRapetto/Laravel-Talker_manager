<?php

namespace App\Http\Controllers;

// use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $req) // LoginRequest no lugar de Request para validar o campos sem middleware

    {
        $password = $req->password;

        $token = Hash::make($password); # Hash somente a título didático

        $jsonString = file_get_contents(base_path('database/talkers.json'));
        $talkers = json_decode($jsonString, true);
        $talkers[0] = $token;

        file_put_contents(base_path('database/talkers.json'), json_encode($talkers));

        return response()->json([
            "message" => $token
        ], 200);
    }

    private function storeToken($path, $token)
    {
        $jsonString = file_get_contents(base_path($path));
        $talkers = json_decode($jsonString, true);

        if (is_string($talkers[0])) {
            $talkers[0] = $token;
        } else {
            array_unshift($talkers, $token);
        }
        file_put_contents(base_path($path), json_encode($talkers));
    }
}