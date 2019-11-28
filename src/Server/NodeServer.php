<?php
namespace Iwan\Server;

use Iwan\Event\NodeEvent;
use Iwan\Util\Crypt;
use Iwan\Throwable\DirectoryException;
use Swoole\Server;

class NodeServer extends AbstractServer
{
    private $crypt;

    /**
     * NodeServer constructor.
     * @param string $ip
     * @param int $port
     * @throws DirectoryException
     */
    public function __construct(string $ip, int $port)
    {
        parent::__construct($ip, $port, 'node');
        $this->crypt = Crypt::load($this->workspace);
    }

    /**
     * @param bool $daemon
     * @return mixed|void
     */
    public function start(bool $daemon = false)
    {
        $server = new Server($this->ip, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
        $server->on('Start', function (Server $server) {
            return $this->onStart($server);
        });
        $server->on('Packet', function (Server $server, string $data, array $client_info) {
            return $this->onPacket($server, $data, $client_info);
        });
    }

    private function onStart(Server $server)
    {
        $event = NodeEvent::create($this, ['public_key' => $this->crypt->getPublicKey()]);
        $data = ['event' => serialize($event)];
        $data['sign'] = $this->crypt->sign($data['event']);
        $server->sendto('ip', 'port', $this->crypt->encrypt(serialize($data), 'publicKey'));
    }

    private function onPacket(Server $server, string $data, array $client_info)
    {
        $data = unserialize($this->crypt->decrypt($data));
        if ($this->crypt->verify($data['event'], $data['sign'], 'publicKey')) {
            $event = unserialize($data['event']);
        }
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return "{$this->ip}:{$this->port}";
    }
}
