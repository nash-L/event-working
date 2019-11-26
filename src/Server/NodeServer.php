<?php
namespace Iwan\Server;

use Iwan\Event\NodeEvent;
use Iwan\Util\Directory;
use phpseclib\Crypt\RSA;
use Swoole\Server;

class NodeServer extends AbstractServer
{
    /**
     * @var string
     */
    private $privateKey;

    public function __construct(string $ip, int $port)
    {
        parent::__construct($ip, $port, 'node');
    }

    /**
     * @param bool $daemon
     * @return mixed|void
     */
    public function start(bool $daemon = false)
    {
        // TODO: Implement start() method.
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return "{$this->ip}:{$this->port}";
    }
}
