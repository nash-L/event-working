<?php
namespace Iwan\Server;

use Iwan\Util\Directory;
use Iwan\Throwable\DirectoryException;

abstract class AbstractServer
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var int
     */
    protected $pid;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var Directory
     */
    protected $workspace;

    /**
     * AbstractServer constructor.
     * @param string $ip
     * @param int $port
     * @param string|null $serverName
     */
    public function __construct(string $ip, int $port, ?string $serverName = null)
    {
        $this->pid = posix_getpid();
        is_null($serverName) && $serverName = $this->pid;
        swoole_set_process_name('php-ps:' . $serverName);
        $this->ip = $ip;
        $this->port = $port;
    }

    /**
     * @param string $workspace
     * @return AbstractServer
     * @throws DirectoryException
     */
    public function setWorkspace(string $workspace): AbstractServer
    {
        $this->workspace = new Directory($workspace);
        return $this;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function set(array $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param bool $daemon
     * @return mixed
     */
    abstract public function start(bool $daemon = false);
}
