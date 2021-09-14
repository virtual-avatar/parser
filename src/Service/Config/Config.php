<?php
namespace App\Service\Config;

use Dotenv\Dotenv;

class Config
{
    static function init() {

        $dotenv = Dotenv::createImmutable(".");
        $dotenv->load();
    }
}