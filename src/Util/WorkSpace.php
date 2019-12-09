<?php
namespace iWan\Util;

use iWan\Throwable\DirectoryException;
use Noodlehaus\Config;
use Noodlehaus\Parser\Json;
use Symfony\Component\Filesystem\Filesystem;

class WorkSpace
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $root_dir;
    /**
     * @var string
     */
    private $runtime_dir = '/runtime';
    /**
     * @var string
     */
    private $catch_dir = '/runtime/catch';
    /**
     * @var string
     */
    private $cert_dir = '/runtime/cert';
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Crypt
     */
    private $crypt;

    /**
     * WorkSpace constructor.
     * @param string $workSpace
     * @throws DirectoryException
     */
    public function __construct(string $workSpace)
    {
        if (!is_dir($workSpace)) {
            throw new DirectoryException('路径“' . $workSpace . '”不存在或是一个文件，请使用一个有效的文件夹路径。');
        }
        $this->root_dir = realpath($workSpace);
        $this->filesystem = new Filesystem();
        $this->init();
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return Crypt
     */
    public function getCrypt(): Crypt
    {
        return $this->crypt;
    }

    /**
     *
     */
    private function init()
    {
        $this->initDirectory();
        $this->initConfig();
        $this->initCrypt();
    }

    /**
     *
     */
    private function initDirectory()
    {
        if (!$this->filesystem->exists($runtime_dir = $this->root_dir . $this->runtime_dir)) {
            $this->filesystem->mkdir($runtime_dir, 0755);
        }
        if (!$this->filesystem->exists($catch_dir = $this->root_dir . $this->catch_dir)) {
            $this->filesystem->mkdir($catch_dir, 0755);
        }
        if (!$this->filesystem->exists($cert_dir = $this->root_dir . $this->cert_dir)) {
            $this->filesystem->mkdir($cert_dir, 0755);
        }
    }

    /**
     *
     */
    private function initConfig()
    {
        $defaultConfig = [];
        $this->config = Config::load(json_encode($defaultConfig), new Json, true);
        if ($this->filesystem->exists($this->root_dir . '/config.json')) {
            $this->config->merge(Config::load($this->root_dir . '/config.json', new Json));
        }
    }

    /**
     *
     */
    private function initCrypt()
    {
        if ($this->filesystem->exists($privateKeyPath = $this->root_dir . $this->cert_dir . '/private.pem')) {
            $this->crypt = Crypt::loadKey(file_get_contents($privateKeyPath));
        } else {
            $this->crypt = Crypt::create();
            $this->filesystem->dumpFile($this->root_dir . $this->cert_dir . '/private.pem', $this->crypt->getPrivateKey());
            $this->filesystem->dumpFile($this->root_dir . $this->cert_dir . '/public.pem', $this->crypt->getPublicKey());
        }
    }
}
