<?php
namespace Iwan;

use Iwan\Util\Directory;
use Swoole\Event;
use Swoole\Process;

class Service
{
    /**
     * @var string
     */
    private $pidFile = 'tmp/pid.txt';

    /**
     * @var array
     */
    private $processList;

    /**
     * @var int
     */
    private $manager_pid;

    /**
     * @var array
     */
    private $server;

    /**
     * @var Directory
     */
    protected $workspace;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        swoole_set_process_name('php-ps:master');
        $this->processList = [];
        $this->manager_pid = posix_getpid();
    }

    /**
     * @param array $server
     * @return Service
     */
    public function setServer(array $server): Service
    {
        $this->server = $server;
        return $this;
    }

    /**
     * @param string $workspace
     * @return Service
     * @throws Throwable\DirectoryException
     */
    public function setWorkspace(string $workspace): Service
    {
        $this->workspace = new Directory($workspace);
        return $this;
    }

    /**
     * @param bool $daemon
     * @throws Throwable\DirectoryException
     */
    public function start(bool $daemon = false)
    {
        if ($daemon) {
            Process::daemon();
            $this->manager_pid = getmypid();
        }
        foreach ($this->server as $serverName => $serverSetting) {
            $this->createProcess(count($this->processList), function (Process $process) use ($serverName, $serverSetting) {
                $file = realpath(BIN_DIR . '/start');
                $args = ['service', $serverName];
                $execFile = realpath($_SERVER['_']);
                if ($execFile !== $file) {
                    array_unshift($args, $file);
                }
                $process->exec($execFile, $args);
            });
        }
        $this->processWait();
    }

    /**
     * @param int $index
     * @param callable|null $func
     */
    private function createProcess(int $index, ?callable $func = null): void
    {
        if (isset($this->processList[$index]) && is_null($func)) {
            $func = $this->processList[$index][1];
        }
        $process = new Process($func, true);
        $this->processList[$index] = [$process->start(), $func, $process];
        // 接收子进程输出
        Event::add($process->pipe, function () use ($process) {
            $recv = $process->read();
        });
    }

    /**
     * @throws Throwable\DirectoryException
     */
    private function processWait()
    {
        $this->workspace->echo($this->pidFile, $this->manager_pid);
        Process::signal(SIGCHLD, function () { $this->rebootProcess(); }); // 进程回收
        Process::signal(SIGTERM, function () { $this->stopProcess(); }); // 进程终止
        Event::wait(); // 事件轮询
    }

    /**
     *
     */
    public function stop()
    {
        Process::kill($this->workspace->cat($this->pidFile), SIGTERM);
    }

    /**
     * @throws Throwable\DirectoryException
     */
    public function reboot()
    {
        $this->stop();
        while (true) {
            if (!Process::kill($this->workspace->cat($this->pidFile), 0)) {
                break;
            }
        }
        $this->start();
    }

    /**
     *
     */
    private function rebootProcess()
    {
        while ($ret = Process::wait(false)) {
            foreach ($this->processList as $index => $processData) {
                if (intval($ret['pid']) === $processData[0]) {
                    $this->createProcess($index);
                    break;
                }
            }
        }
    }

    /**
     *
     */
    private function stopProcess()
    {
        foreach ($this->processList as $index => $processData) {
            unset($this->processList[$index]);
            Process::kill($processData[0], SIGTERM);
        }
        Process::signal(SIGTERM, null);
        Process::kill($this->manager_pid, SIGTERM);
    }
}
