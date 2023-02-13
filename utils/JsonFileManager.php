<?php

namespace utils;

class JsonFileManager
{
  public function readJsonFile($path)
  {
    $jsonString = file_get_contents(base_path($path));
    return json_decode($jsonString, true);
  }

  public function saveJsonFile($path, $content)
  {
    file_put_contents(base_path($path), json_encode($content));
  }

  public function storeToken($path, $token)
  {
    $data = $this->readJsonFile($path);
    $data["token"] = $token;

    $this->saveJsonFile($path, $data);
  }
}