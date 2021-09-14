<?php
namespace App\Controller;


use App\Service\Monolog\Log;
use App\Service\Parser\AbstractParser\Parser;
use App\Service\Parser\Foxtrot\Foxtrot;


class ParserController
{
    public function __invoke()
    {
        $logger = Log::getInstance();
        foreach (glob("config\markets\*.ini") as $filename) {
            $logger->debug("Загружаем конфигурацию из файла " . $filename);
            $config = parse_ini_file(realpath($filename),true);
            if($config === false) {
                $logger->error("Не удалось загрузить данные из файла " . $filename);
                break;
            }
            $market = strtolower($config['name']);
            if(empty($market) && empty($config['parsing_url'])) {
                $logger->error("Конфигурация в " . $filename . "не корректна");
                break;
            } else {
                switch ($market) {
                    case 'foxtrot':
                        $this->runParser(new Foxtrot($config['parsing_url']));
                        break;
                    default :
                        $logger->error("Парсер " . $market . "указаный в файле " . $filename . "не найден");
                        break;
                }
            }
        }
    }

    private function runParser(Parser $parser)
    {
        $parser->run();
    }

}