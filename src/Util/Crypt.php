<?php
namespace Iwan\Util;

use phpseclib\Crypt\RSA;
use Iwan\Throwable\DirectoryException;

class Crypt
{
    /**
     * @var RSA
     */
    private $rsa;

    /**
     * @var array
     */
    private $keys;

    /**
     * @var Directory
     */
    private $directory;

    /**
     * Crypt constructor.
     * @param Directory $directory
     * @param string $fileName
     * @throws DirectoryException
     */
    private function __construct(Directory $directory, string $fileName)
    {
        $this->rsa = new RSA;
        $this->directory = $directory;
        if ($directory->exists($fileName)) {
            $this->createKeys();
            $this->saveKeys($directory->pathName($fileName));
        } else {
            $this->keys = unserialize($directory->cat($fileName));
        }
    }

    /**
     *
     */
    private function createKeys(): void
    {
        $this->rsa->setPrivateKeyFormat(RSA::PRIVATE_FORMAT_PKCS1);
        $this->rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_PKCS1);
        $this->keys = $this->rsa->createKey(1024);
    }

    /**
     * @param string $fileName
     * @throws DirectoryException
     */
    private function saveKeys(string $fileName): void
    {
        $this->directory->echo($fileName, serialize($this->keys));
    }

    /**
     * @param Directory $directory
     * @param string $fileName
     * @return Crypt
     * @throws DirectoryException
     */
    public static function load(Directory $directory, string $fileName = 'cert/rsa_key.pem'): Crypt
    {
        return new Crypt($directory, $fileName);
    }

    /**
     * @param string $plaintext
     * @return string
     */
    public function sign(string $plaintext): string
    {
        $this->rsa->loadKey($this->getPrivateKey());
        $this->rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        return base64_encode($this->rsa->sign($plaintext));
    }

    /**
     * @param string $plaintext
     * @param string $signature
     * @param string|null $remotePublicKey
     * @return bool
     */
    public function verify(string $plaintext, string $signature, ?string $remotePublicKey = null): bool
    {
        $this->rsa->loadKey(is_null($remotePublicKey)?$this->getPublicKey():$remotePublicKey);
        $this->rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        return $this->rsa->verify($plaintext, base64_decode($signature));
    }

    /**
     * @param string $plaintext
     * @param string|null $remotePublicKey
     * @return string
     */
    public function encrypt(string $plaintext, ?string $remotePublicKey = null): string
    {
        $this->rsa->loadKey(is_null($remotePublicKey)?$this->getPublicKey():$remotePublicKey);
        $this->rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        return $this->rsa->encrypt($plaintext);
    }

    /**
     * @param string $plaintext
     * @return string
     */
    public function decrypt(string $plaintext): string
    {
        $this->rsa->loadKey($this->getPrivateKey());
        $this->rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        return $this->rsa->decrypt($plaintext);
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->keys['publickey'];
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->keys['privatekey'];
    }
}