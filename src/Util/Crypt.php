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

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->privateKey;
    }

    /**
     * @return bool|string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public static function loadKey(string $privateKey): self
    {
        return new static($privateKey);
    }

    public static function create(): self
    {
        $rsa = new RSA();
        $rsa->setPrivateKeyFormat(RSA::PRIVATE_FORMAT_PKCS1);
        $rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_PKCS1);
        $keys = $rsa->createKey(1024);
        return self::loadKey($keys['privatekey']);
    }
}
