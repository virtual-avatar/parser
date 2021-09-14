<?php
namespace App\Service\Parser\AbstractParser;

use App\Service\Parser\InterfaceParser\ParserWorker;

abstract class Parser
{
    abstract public function getParser(): ParserWorker;

    public function run(): void
    {
        // Вызываем фабричный метод для создания объекта
        $parser = $this->getParser();
        $parser->run();
    }
}