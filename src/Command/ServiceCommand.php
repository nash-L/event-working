<?php
namespace Iwan\Command;

use Iwan\Server\NodeServer;
use Iwan\Server\UserServer;
use Iwan\Service;
use Iwan\Throwable\RuntimeException;
use Noodlehaus\Config;
use Noodlehaus\Parser\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Iwan\Throwable\DirectoryException;

class ServiceCommand extends Command
{
    protected static $defaultName = 'service';

    protected function configure()
    {
        $this->setDescription('Service manager')
            ->setHelp('This command helps you manage your services of your project')
            ->addOption('workspace', 'w', InputOption::VALUE_OPTIONAL, 'Working directory', '.')
            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'Daemon mode')
            ->addArgument('action', InputArgument::OPTIONAL, 'Service action', 'start');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');
        if (method_exists($this, $action)) {
            $workspace = realpath($input->getOption('workspace'));
            if (!$workspace) {
                throw new RuntimeException('No workspace found');
            }
            $this->$action($workspace, $input, $output);
        }
    }

    /**
     * @param string $projectDirectory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws DirectoryException
     */
    private function start(string $projectDirectory, InputInterface $input, OutputInterface $output)
    {
        $config = new Config($projectDirectory . '/conf.json', new Json);
        (new Service())->setServer($config->all())
            ->setWorkspace($projectDirectory)
            ->start($input->getOption('daemon'));
    }

    /**
     * @param string $projectDirectory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws DirectoryException
     */
    private function stop(string $projectDirectory, InputInterface $input, OutputInterface $output)
    {
        $config = new Config($projectDirectory . '/conf.json', new Json);
        (new Service())->setServer($config->all())
            ->setWorkspace($projectDirectory)
            ->stop();
    }

    /**
     * @param string $projectDirectory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws DirectoryException
     */
    private function restart(string $projectDirectory, InputInterface $input, OutputInterface $output)
    {
        $config = new Config($projectDirectory . '/conf.json', new Json);
        (new Service())->setServer($config->all())
            ->setWorkspace($projectDirectory)
            ->reboot();
    }

    /**
     * @param string $projectDirectory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws DirectoryException
     */
    private function node(string $projectDirectory, InputInterface $input, OutputInterface $output)
    {
        $config = (new Config($projectDirectory . '/conf.json', new Json))->get('node');
        (new NodeServer($config['ip'], intval($config['port'])))->set($config['setting'])
            ->setWorkspace($projectDirectory)
            ->start($input->getOption('daemon'));
    }

    /**
     * @param string $projectDirectory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws DirectoryException
     */
    private function server(string $projectDirectory, InputInterface $input, OutputInterface $output)
    {
        $config = (new Config($projectDirectory . '/conf.json', new Json))->get('server');
        (new UserServer($config['type'], $config['ip'], intval($config['port'])))->set($config['setting'])
            ->setWorkspace($projectDirectory)
            ->start($input->getOption('daemon'));
    }
}
