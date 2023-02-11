<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;


class TalkerController extends Controller
{
    private $path = 'database/talkers.json';


    private function readJsonFile()
    {
        $jsonString = file_get_contents(base_path($this->path));
        return json_decode($jsonString, true);
    }

    private function getTokenFromJsonFile()
    {
        $talkers = $this->readJsonFile();
        return $talkers[0];
    }

    private function verifyToken($requesToken)
    {
        $token = $this->getTokenFromJsonFile();
        if (!$requesToken)
            return ["message" => "Token não encontrado!"];
        if ($token !== $requesToken)
            return ["message" => "Token inválido!"];
        return false;
    }

    private function verifyName($name)
    {
        if (!$name)
            return ["message" => "O campo name é obrigatório!"];
        if (strlen($name) < 3)
            return ["message" => "O name deve ter pelo menos 3 caracteres"];
        return false;
    }

    private function verifyAge($age)
    {
        if (!$age)
            return ["message" => "O campo age é obrigatório!"];
        if ($age < 18)
            return ["message" => "A pessoa palestrante deve ser maior de idade"];
        return false;
    }

    private function verifyTalk($talk)
    {
        if (!$talk)
            return ["message" => "O campo talk é obrigatório!"];
        if (!array_key_exists("rate", $talk))
            return ["message" => "O campo rate é obrigatório!"];
        if (!array_key_exists("watchedAt", $talk))
            return ["message" => "O campo watchedAt é obrigatório!"];
        return false;
    }

    private function verifyTalkRate($rate)
    {
        if ($rate < 1 || $rate > 5)
            return ["message" => "O campo rate deve ser um número de 1 à 5"];
        return false;
    }

    private function verifyTalkWatchedAt($date)
    {
        $regex = "/^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/";
        $isValidDate = preg_match($regex, $date);

        if (!$isValidDate)
            return ["message" => "O campo watchedAt deve ter o formato dd/mm/aaaa"];

        return false;
    }

    private function verifyFields($authorization, $name, $age, $talk)
    {
        $isNotValidToken = $this->verifyToken($authorization);
        if ($isNotValidToken)
            return response()->json($isNotValidToken, 401);

        $isNotValidName = $this->verifyName($name);
        if ($isNotValidName)
            return response()->json($isNotValidName, 400);

        $isNotValidAge = $this->verifyAge($age);
        if ($isNotValidAge)
            return response()->json($isNotValidAge, 400);

        $isNotValidTalk = $this->verifyTalk($talk);
        if ($isNotValidTalk)
            return response()->json($isNotValidTalk, 400);

        $isNotValidTalkRate = $this->verifyTalkRate($talk['rate']);
        if ($isNotValidTalkRate)
            return response()->json($isNotValidTalkRate, 400);

        $isNotValidTalkWatch = $this->verifyTalkWatchedAt($talk['watchedAt']);
        if ($isNotValidTalkWatch)
            return response()->json($isNotValidTalkWatch, 400);
    }

    public function getAllTalkers()
    {
        $talkers = $this->readJsonFile();
        if (is_string($talkers[0]))
            array_shift($talkers);
        return response()->json($talkers, 200);
    }

    public function getTalker($id)
    {
        $talkers = $this->readJsonFile();

        foreach ($talkers as $talker) {
            if (is_string($talker))
                continue;
            if ($talker['id'] === intval($id))
                return response()->json($talker, 200);
        }

        return response()->json([
            "message" => "Talker not found!",
        ], 404);
    }

    public function createTalker(Request $request)
    {
        $authorization = $request->header('Authorization');
        $name = $request->name;
        $age = $request->age;
        $talk = $request->talk;

        $isNotValidRequest = $this->verifyFields($authorization, $name, $age, $talk);

        if ($isNotValidRequest) {
            return $isNotValidRequest;
        }

        $talkers = $this->readJsonFile();
        array_push($talkers, [
            "id" => count($talkers) === 1 ? 1 : $talkers[count($talkers) - 1]["id"] + 1,
            "name" => $name,
            "age" => $age,
            "talk" => $talk,
        ]);

        file_put_contents(base_path($this->path), json_encode($talkers));

        return response()->json([
            "message" => "Palestrante criado com sucesso!",
            "talker" => $talkers[count($talkers) - 1]
        ]);
    }

    public function updateTalker(Request $request, $id)
    {
        $authorization = $request->header('Authorization');
        $name = $request->name;
        $age = $request->age;
        $talk = $request->talk;

        $isNotValidRequest = $this->verifyFields($authorization, $name, $age, $talk);

        if ($isNotValidRequest) {
            return $isNotValidRequest;
        }

        $talkers = $this->readJsonFile();

        $talkerFoundedIndex = 0;
        for ($i = 1; $i < count($talkers); $i += 1) {
            global $talkerFoundedIndex;
            if ($talkers[$i]["id"] === intval($id)) {
                $talkers[$i]["name"] = $name;
                $talkers[$i]["age"] = $age;
                $talkers[$i]["talk"] = $talk;

                $talkerFoundedIndex = $i;
            }
        }

        file_put_contents(base_path($this->path), json_encode($talkers));

        if (!$talkerFoundedIndex)
            return response()->json([
                "message" => "Talker not found!",
            ], 404);

        return response()->json([
            "message" => "Palestrante criado com sucesso!",
            "talker" => $talkers[$talkerFoundedIndex]
        ]);
    }

    public function deleteTalker(Request $request, $id)
    {
        $authorization = $request->header('authorization');

        $isNotValidToken = $this->verifyToken($authorization);
        if ($isNotValidToken)
            return response()->json($isNotValidToken, 401);

        $talkers = $this->readJsonFile();
        $filteredTalkers = [];
        for ($i = 0; $i < count($talkers); $i += 1) {
            if (is_string($talkers[$i])) {
                array_unshift($filteredTalkers, $talkers[$i]);
                continue;
            }
            if ($talkers[$i]['id'] !== intval($id)) {
                array_push($filteredTalkers, $talkers[$i]);
            }
        }

        file_put_contents(base_path($this->path), json_encode($filteredTalkers));

        return response('No Content', 204);
    }

    public function searchTalker(Request $request)
    {
        $searchTerm = $request->query('q');
        $authorization = $request->header('authorization');

        $isNotValidToken = $this->verifyToken($authorization);
        if ($isNotValidToken)
            return response()->json($isNotValidToken, 401);

        $talkers = $this->readJsonFile();

        $filteredTalkers = Arr::where($talkers, function ($value, $key) use ($searchTerm) {
            if (is_string($value))
                return false;
            return str_contains($value['name'], $searchTerm) && true;
        });

        return response()->json($filteredTalkers, 200);
    }
}