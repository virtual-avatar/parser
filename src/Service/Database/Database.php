<?php
namespace App\Service\Database;

class Database
{
    private static ?\Doctrine\DBAL\Connection $conn = null;

    private function __construct(){
        $connectionParams = [
            'url' => $_ENV['DATABASE_URL'],
        ];
        self::$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
    }

    public static function getInstance()
    {
        if(self::$conn == NULL) {
            new Database();
        }
        return self::$conn;
    }
}