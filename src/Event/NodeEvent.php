<?php
namespace Iwan\Event;


use Iwan\Server\NodeServer;

class NodeEvent
{
    private $eventData;

    private function __construct($eventData)
    {
        $this->eventData = $eventData;
    }

    public function getUuid(): string
    {
        return $this->eventData['uuid'];
    }

    public function getTarget(): string
    {
        return $this->eventData['target'];
    }

    public function getTime(): int
    {
        return $this->eventData['time'];
    }

    public function getDate(): string
    {
        return $this->eventData['date'];
    }

    public function getParam(): array
    {
        return $this->eventData['param'];
    }

    public static function create(NodeServer $server, array $param): NodeEvent
    {
        $target = $server->getAddress();
        return new NodeEvent([
            'uuid' => uniqid($target, true),
            'target' => $target,
            'time' => time(),
            'date' => date('Y-m-d'),
            'param' => $param
        ]);
    }
}
