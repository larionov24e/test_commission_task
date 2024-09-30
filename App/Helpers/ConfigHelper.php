<?php

namespace App\Helpers;

class ConfigHelper
{
    public function get(string $key)
    {
        return require __DIR__ . '/../Config/' . $key . '.php';
    }
}