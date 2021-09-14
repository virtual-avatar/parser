<?php
namespace App\Console\Commands;

use App\Controller\ParserController;
use App\Service\Monolog\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParserGoodsCommand extends Command
{
    protected function configure()
    {
        $this->setName('parser')
            ->setDescription('parser goods from market')
            ->setHelp('parser goods from market commands');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parser = new ParserController();

        try{
            $parser();
        } catch (\Exception $e) {
            Log::error($e);
        }

        return Command::SUCCESS;
    }
}