<?php
namespace iWan\Util;

use iWan\Throwable\DirectoryException;
use Symfony\Component\Filesystem\Filesystem;

class WorkSpace
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    private $root_dir;
    private $runtime_dir = '/runtime';
    private $tmp_dir = '/runtime/tmp';
    private $catch_dir = '/runtime/catch';

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
        $this->initDir($this->runtime_dir);
        $this->initDir($this->tmp_dir);
        $this->initDir($this->catch_dir);
    }

    private function initDir($dir)
    {
        if (!$this->filesystem->exists($dir = $this->root_dir . $dir)) {
            $this->filesystem->mkdir($dir);
        }
    }
}
