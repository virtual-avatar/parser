<?php
namespace App\Service\Parser\Foxtrot;

use App\Service\Parser\AbstractParser\Parser;
use App\Service\Parser\FoxtrotParser;
use App\Service\Parser\InterfaceParser\ParserWorker;

class Foxtrot extends Parser
{
    private array $url;

    /**
     * FoxtroxParser constructor.
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getParser(): ParserWorker
    {
        return new FoxtrotParser($this->url);
    }
}