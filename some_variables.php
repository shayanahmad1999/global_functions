<?php

const HELP_GUIDE = "laravel.com";
define("GREETING", "Welcome to W3Schools.com!");
echo GREETING . "\n";
echo HELP_GUIDE . "\n";

const SITE_NAME_FIRST = "My Website";
const PI = 3.14159;

function calculateArea($radius)
{
    return PI * $radius * $radius;
}

echo "Welcome to " . SITE_NAME_FIRST . "!\n";
echo "The area of a circle with radius 5 is: " . calculateArea(5) . "\n";

// Using define()
define('SITE_NAME', 'LaravelLMS');
echo SITE_NAME . "\n"; // LaravelLMS

// Using const
const VERSION = '1.0.0';
echo VERSION . "\n"; // 1.0.0

class AppConfig
{
    const TIMEOUT = 30;
    public function getTimeout()
    {
        return self::TIMEOUT;
    }
}

$config = new AppConfig();
echo "Timeout from AppConfig: " . $config->getTimeout() . "\n";

$a = 5;
$b = 10;

function sum()
{
    global $a, $b;
    echo "Sum is: " . ($a + $b) . "\n"; // 15
}
sum();

$x = 7;
$y = 3;

function multiply()
{
    $GLOBALS['result'] = $GLOBALS['x'] * $GLOBALS['y'];
}
multiply();
echo "Multiplication result: " . $result . "\n"; // 21
