<?php

namespace App\Http\Controllers;

// use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use utils\JsonFileManager;

class LoginController extends Controller
{
    public function login(Request $req, JsonFileManager $dataManager) // LoginRequest no lugar de Request para validar o campos sem middleware

    {
        $token = Hash::make($req->password); # Hash somente a título didático
        $dataManager->storeToken('database/talkers.json', $token);

        return response()->json([
            "message" => $token
        ], 200);
    }

// private function storeToken($path, $token)
// {
//     $jsonString = file_get_contents(base_path($path));
//     $talkers = json_decode($jsonString, true);

//     $talkers["token"] = $token;

//     file_put_contents(base_path($path), json_encode($talkers));
// }
}