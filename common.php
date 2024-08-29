<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require 'constants.php';

class DisplayableException extends Exception {}

set_exception_handler(function($exception) {
    echo $exception->getMessage();
});

function loadEnv() {
    $envFilePath = BASE_DIR . '/.env';

    if (is_readable($envFilePath)) {
        $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim(trim($value), "\"");

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("$name=$value");

                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    } else {
        throw new DisplayableException('.env file not found!');
    }
}

function initCommonMethods() {
    loadEnv();
}
