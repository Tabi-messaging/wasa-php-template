<?php

require __DIR__ . '/../vendor/autoload.php';

use Wasa\Env;
use Wasa\Router;

Env::load(__DIR__ . '/../.env');
Router::run();
