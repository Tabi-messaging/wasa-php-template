<?php

namespace Wasa;

class Router
{
    public static function run(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ($uri === '/api' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            require __DIR__ . '/../routes/api.php';
            return;
        }

        require __DIR__ . '/../templates/app.php';
    }
}
