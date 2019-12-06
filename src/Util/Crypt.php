<?php
namespace iWan\Util;

use phpseclib\Crypt\RSA;

class Crypt
{
    /**
     * @var RSA
     */
    private $rsa;

    private $publicKey;

    private $privateKey;

    private function __construct(string $privateKey)
    {
        $this->rsa = new RSA();
        $this->privateKey = $privateKey;
        $this->rsa->loadKey($privateKey);
        $this->rsa->setPrivateKey();
        $this->publicKey = $this->rsa->getPublicKey();
    }

    public static function loadKey(string $privateKey): self
    {
        return new static($privateKey);
    }
}
