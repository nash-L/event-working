<?php
namespace Iwan\Command;

use Noodlehaus\Config;
use Noodlehaus\Parser\Ini;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    protected static $defaultName = 'init';

    protected function configure()
    {
        $this->setDescription('Initialize project')
            ->setHelp('This command helps you build the structure of your project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectDirectory = realpath('.');
        $config = new Config($projectDirectory . '/conf.ini', new Ini);
        var_dump($config->get('node'));
    }
}