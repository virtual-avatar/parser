<?php
namespace App\Service\Monolog;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log
{
    private static $looger = null;

    private function __construct()
    {

        self::$looger = new Logger('command-log');
        self::$looger->pushHandler(new StreamHandler('log\error.log', Logger::ERROR));
        self::$looger->pushHandler(new StreamHandler('log\debug.log', Logger::DEBUG));

    }

    public static function getInstance()
    {
        if (self::$looger == NULL) {
            new Log();
        }
        return self::$looger;
    }

    public static function error(\Exception $e)
    {
        $logger = Log::getInstance();
        $logger->error("Error Code:" . $e->getCode() . "Message: " . $e->getMessage());
    }

}