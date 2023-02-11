<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $req)
    {
        $email = $req->email;
        $password = $req->password;

        $isNotValidEmail = $this->verifyEmail($email);
        $isNotValidPassword = $this->verifyPassword($password);

        if ($isNotValidEmail || $isNotValidPassword)
            return Response()->json($isNotValidEmail ? $isNotValidEmail : $isNotValidPassword, 400);


        $token = Hash::make($password); # Hash somente a título didático

        $jsonString = file_get_contents(base_path('database/talkers.json'));
        $talkers = json_decode($jsonString, true);
        $talkers[0] = $token;

        file_put_contents(base_path('database/talkers.json'), json_encode($talkers));

        return response()->json([
            "message" => $token
        ], 200);
    }

    private function verifyEmail($email)
    {
        if (!$email)
            return ["message" => "O campo email é obrigatório!"];

        $regex = "/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/";
        $isValidEmail = preg_match($regex, $email);

        if (!$isValidEmail)
            return ["message" => "O email deve ser válido"];

        return false;
    }

    private function verifyPassword($password)
    {
        if (!$password)
            return ["message" => "O campo password é obrigatório!"];
        if (strlen($password) < 6)
            return ["message" => "O password deve conter pelo menos 6 caracteres"];
        return false;
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