<?php

namespace Opensoft\StorageBundle\Storage\Gaufrette\Stream;

use Gaufrette\Stream;
use Gaufrette\StreamMode;

/**
 * Overrides for mkdir mode until PR is merged on main project
 *
 * @see https://github.com/KnpLabs/Gaufrette/pull/320
 *
 * @author Antoine Hérault <antoine.herault@gmail.com>
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class Local implements Stream
{
    private $path;
    /** @var  StreamMode */
    private $mode;
    private $fileHandle;
    private $mkdirMode;

    /**
     * Constructor
     *
     * @param string $path
     * @param int    $mkdirMode
     */
    public function __construct($path, $mkdirMode = 0755)
    {
        $this->path = $path;
        $this->mkdirMode = $mkdirMode;
    }

    /**
     * {@inheritDoc}
     */
    public function open(StreamMode $mode)
    {
        $baseDirPath = dirname($this->path);
        if ($mode->allowsWrite() && !is_dir($baseDirPath)) {
            @mkdir($baseDirPath, $this->mkdirMode, true);
        }
        try {
            $fileHandle = @fopen($this->path, $mode->getMode());
        } catch (\Exception $e) {
            $fileHandle = false;
        }

        if (false === $fileHandle) {
            throw new \RuntimeException(sprintf('File "%s" cannot be opened', $this->path));
        }

        $this->mode = $mode;
        $this->fileHandle = $fileHandle;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($count)
    {
        if (!$this->fileHandle) {
            return false;
        }

        if (false === $this->mode->allowsRead()) {
            throw new \LogicException('The stream does not allow read.');
        }

        return fread($this->fileHandle, $count);
    }

    /**
     * {@inheritDoc}
     */
    public function write($data)
    {
        if (!$this->fileHandle) {
            return false;
        }

        if (false === $this->mode->allowsWrite()) {
            throw new \LogicException('The stream does not allow write.');
        }

        return fwrite($this->fileHandle, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        if (!$this->fileHandle) {
            return false;
        }

        $closed = fclose($this->fileHandle);

        if ($closed) {
            $this->mode = null;
            $this->fileHandle = null;
        }

        return $closed;
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        if ($this->fileHandle) {
            return fflush($this->fileHandle);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if ($this->fileHandle) {
            return 0 === fseek($this->fileHandle, $offset, $whence);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function tell()
    {
        if ($this->fileHandle) {
            return ftell($this->fileHandle);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function eof()
    {
        if ($this->fileHandle) {
            return feof($this->fileHandle);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function stat()
    {
        if ($this->fileHandle) {
            return fstat($this->fileHandle);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function cast($castAs)
    {
        if ($this->fileHandle) {
            return $this->fileHandle;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function unlink()
    {
        if ($this->mode && $this->mode->impliesExistingContentDeletion()) {
            return @unlink($this->path);
        }

        return false;
    }
}
