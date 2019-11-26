<?php


namespace Iwan\Server;


class UserServer extends AbstractServer
{
    public function __construct(string $type, string $ip, int $port)
    {
        parent::__construct($ip, $port, 'user');
    }

    public function start(bool $daemon = false)
    {
        // TODO: Implement start() method.
    }
}