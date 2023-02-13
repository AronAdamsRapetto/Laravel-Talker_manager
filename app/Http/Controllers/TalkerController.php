<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use utils\JsonFileManager;


class TalkerController extends Controller
{
    private $path = 'database/talkers.json';

    private function readJsonFile()
    {
        $jsonString = file_get_contents(base_path($this->path));
        return json_decode($jsonString, true);
    }

    private function verifyToken($requestToken)
    {
        $data = $this->readJsonFile();
        if (!$requestToken)
            return response()->json(["message" => "Token não encontrado!"], 401);
        if ($data["token"] !== $requestToken)
            return response()->json(["message" => "Token inválido!"], 401);
        return false;
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

    public function createTalker(Request $request, JsonFileManager $dataManager)
    {
        $authorization = $request->header('Authorization');
        $isNotAuthorized = $this->verifyToken($authorization);

        if ($isNotAuthorized)
            return $isNotAuthorized;

        $data = $this->readJsonFile();
        array_push($data["talkers"], [
            "id" => count($data["talkers"]) === 0 ? 1 : $data["talkers"][count($data["talkers"]) - 1]["id"] + 1,
            ...$request->all()
        ]);

        file_put_contents(base_path($this->path), json_encode($data));

        return response()->json([
            "message" => "Palestrante criado com sucesso!",
            "talker" => $data["talkers"][count($data["talkers"]) - 1],
        ]);
    }

    public function updateTalker(Request $request, $id)
    {
        $authorization = $request->header('Authorization');
        $isNotAuthorized = $this->verifyToken($authorization);

        if ($isNotAuthorized)
            return $isNotAuthorized;

        $talkers = $this->readJsonFile();

        $talkerFoundedIndex = 0;
        for ($i = 1; $i < count($talkers); $i += 1) {
            global $talkerFoundedIndex;
            if ($talkers[$i]["id"] === intval($id)) {
                $talkers[$i]["name"] = $request->name;
                $talkers[$i]["age"] = $request->age;
                $talkers[$i]["talk"] = $request->talk;

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
        $authorization = $request->header('Authorization');
        $isNotAuthorized = $this->verifyToken($authorization);

        if ($isNotAuthorized)
            return $isNotAuthorized;

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
        $authorization = $request->header('Authorization');

        $isNotAuthorized = $this->verifyToken($authorization);
        if ($isNotAuthorized)
            return $isNotAuthorized;

        $talkers = $this->readJsonFile();

        $filteredTalkers = Arr::where($talkers, function ($value, $key) use ($searchTerm) {
            if (is_string($value))
                return false;
            return str_contains($value['name'], $searchTerm) && true;
        });

        return response()->json($filteredTalkers, 200);
    }
}