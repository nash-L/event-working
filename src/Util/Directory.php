<?php


namespace Iwan\Util;

use Iwan\Throwable\DirectoryException;
use Throwable;

class Directory
{
    /**
     * @var string
     */
    private $root_dir;

    /**
     * Directory constructor.
     * @param string $root
     * @throws DirectoryException
     */
    public function __construct(string $root = '.')
    {
        $this->root_dir = realpath('.');
        if ($root !== '.' && $this->mkDir($root)) {
            $this->root_dir = realpath($root);
        }
    }

    /**
     * @param string $dir
     * @throws DirectoryException
     */
    public function cd(string $dir)
    {
        $this->mkDir($dir);
        $this->root_dir = $this->pathName($dir);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        if ($path[0] === '/') {
            return realpath($path) && true;
        }
        return realpath($this->root_dir . '/' . $path) && true;
    }

    /**
     * @param string $path
     * @return string|null
     */
    public function pathName(string $path): ?string
    {
        if ($this->exists($path)) {
            return null;
        }
        if ($path[0] === '/') {
            return realpath($path);
        }
        return realpath($this->root_dir . '/' . $path);
    }

    /**
     * @param string $path
     * @return string|null
     */
    public function dirName(string $path): ?string
    {
        if ($result = $this->pathName($path)) {
            return dirname($result);
        }
        return null;
    }

    /**
     * @param string $filePath
     * @return string|null
     */
    public function cat(string $filePath): ?string
    {
        if ($this->exists($filePath)) {
            return file_get_contents($this->pathName($filePath));
        }
        return null;
    }

    /**
     * @param string $filePath
     * @param string $data
     * @return bool
     * @throws DirectoryException
     */
    public function echo(string $filePath, string $data): bool
    {
        if (!$this->exists($filePath)) {
            $filePathArr = explode('/', $filePath);
            $fileName = array_pop($filePathArr);
            $filePath = implode('/', $filePathArr);
            $this->mkDir($filePath);
            return file_put_contents($filePath . '/' . $fileName, $data) && true;
        }
        return file_put_contents($this->pathName($filePath), $data) && true;
    }

    /**
     * @param string $dir
     * @return bool
     * @throws DirectoryException
     */
    public function mkDir(string $dir)
    {
        if ($this->exists($dir)) {
            return true;
        }
        $baseDir = $this->root_dir;
        $dirPath = explode('/', $dir);
        if ($dirPath[0] === '') {
            $baseDir = array_shift($dirPath);
        }
        try {
            foreach ($dirPath as $dirName) {
                $baseDir .= "/{$dirName}";
                if (!is_dir($baseDir)) {
                    mkdir($baseDir);
                }
            }
        } catch (Throwable $e) {
            throw new DirectoryException('Unable to create directory');
        }
        return true;
    }
}