<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use utils\JsonFileManager;


class TalkerController extends Controller
{
    private $path = 'database/talkers.json';

    private function verifyToken($storedToken, $requestToken)
    {
        if (!$requestToken)
            return response()->json(["message" => "Token não encontrado!"], 401);
        if ($storedToken !== $requestToken)
            return response()->json(["message" => "Token inválido!"], 401);
        return false;
    }

    public function getAllTalkers(JsonFileManager $dataManager)
    {
        $data = $dataManager->readJsonFile($this->path);
        return response()->json($data["talkers"], 200);
    }

    public function getTalker(JsonFileManager $dataManager, $id)
    {
        $data = $dataManager->readJsonFile($this->path);

        foreach ($data["talkers"] as $talker) {
            if ($talker['id'] === intval($id))
                return response()->json($talker, 200);
        }

        return response()->json([
            "message" => "Talker not found!",
        ], 404);
    }

    public function createTalker(Request $request, JsonFileManager $dataManager)
    {
        $data = $dataManager->readJsonFile($this->path);
        $authorization = $request->header('Authorization');
        $isNotAuthorized = $this->verifyToken($data["token"], $authorization);

        if ($isNotAuthorized)
            return $isNotAuthorized;

        array_push($data["talkers"], [
            "id" => count($data["talkers"]) === 0
            ? 1
            : $data["talkers"][count($data["talkers"]) - 1]["id"] + 1,
            ...$request->all()
        ]);

        $dataManager->saveJsonFile($this->path, $data);

        return response()->json([
            "message" => "Palestrante criado com sucesso!",
            "talker" => $data["talkers"][count($data["talkers"]) - 1],
        ]);
    }

    public function updateTalker(Request $request, JsonFileManager $dataManager, $id)
    {
        $data = $dataManager->readJsonFile($this->path);
        $authorization = $request->header('Authorization');
        $isNotAuthorized = $this->verifyToken($data["token"], $authorization);

        if ($isNotAuthorized)
            return $isNotAuthorized;


        $talkerFoundedIndex = 0;
        for ($i = 0; $i < count($data["talkers"]); $i += 1) {
            global $talkerFoundedIndex;
            if ($data["talkers"][$i]["id"] === intval($id)) {
                $data["talkers"][$i]["name"] = $request->name;
                $data["talkers"][$i]["age"] = $request->age;
                $data["talkers"][$i]["talk"] = $request->talk;

                $talkerFoundedIndex = $i;
            }
        }

        if (!$talkerFoundedIndex)
            return response()->json([
                "message" => "Talker not found!",
            ], 404);

        $dataManager->saveJsonFile($this->path, $data);

        return response()->json([
            "message" => "Palestrante atualizado com sucesso!",
            "talker" => $data["talkers"][$talkerFoundedIndex]
        ]);
    }

    public function deleteTalker(Request $request, JsonFileManager $dataManager, $id)
    {
        $data = $dataManager->readJsonFile($this->path);
        $authorization = $request->header('Authorization');
        $isNotAuthorized = $this->verifyToken($data["token"], $authorization);

        if ($isNotAuthorized)
            return $isNotAuthorized;

        $filteredTalkers = [];
        for ($i = 0; $i < count($data["talkers"]); $i += 1) {
            if ($data["talkers"][$i]['id'] !== intval($id)) {
                array_push($filteredTalkers, $data["talkers"][$i]);
            }
        }

        $dataManager->saveJsonFile($this->path, $data);

        return response('No Content', 204);
    }

    public function searchTalker(Request $request, JsonFileManager $dataManager)
    {
        $data = $dataManager->readJsonFile($this->path);
        $searchTerm = $request->query('q');
        $authorization = $request->header('Authorization');
        $isNotAuthorized = $this->verifyToken($data["token"], $authorization);

        if ($isNotAuthorized)
            return $isNotAuthorized;

        $filteredTalkers = Arr::where($data["talkers"], function ($value, $key) use ($searchTerm) {
            return str_contains($value['name'], $searchTerm) && true;
        });

        return response()->json($filteredTalkers, 200);
    }
}