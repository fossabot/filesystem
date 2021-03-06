<?php

declare(strict_types=1);

namespace Atomastic\Filesystem;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function file_exists;
use function is_dir;
use function mkdir;
use function rename;
use function rmdir;

class Directory
{
    /**
     * Path property
     *
     * Current directory path.
     *
     * @var string|null
     */
    public $path;

    /**
     * Constructor
     *
     * @param string $path Path to directory
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Delete a directory.
     *
     * @param  string $directory Directory to delete.
     * @param  bool   $preserve  The directory itself may be optionally preserved.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function delete(bool $preserve = false): bool
    {
        if (! (new Directory($this->path))->isDirectory()) {
            return false;
        }

        foreach (new FilesystemIterator($this->path) as $item) {
            if ($item->isDir() && ! $item->isLink()) {
                (new Directory($item->getPathname()))->delete();
            } else {
                (new File($item->getPathname()))->delete();
            }
        }

        if ($preserve === false) {
            @rmdir($this->path);
        }

        return true;
    }

    /**
     * Empty the specified directory of all files and directories.
     *
     * @param  string $directory Directory to cleanup.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function clean(): bool
    {
        return $this->delete(true);
    }

    /**
     * Create a directory.
     *
     * @param  int  $mode      The mode is 0777 by default, which means the widest possible access.
     * @param  bool $recursive Allows the creation of nested directories specified in the path.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function create(int $mode = 0755, bool $recursive = false): bool
    {
        return mkdir($this->path, $mode, $recursive);
    }

    /**
     * Move a directory.
     *
     * @param  string $destination The destination path.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function move(string $destination): bool
    {
        return rename($this->path, $destination);
    }

    /**
     * Copy a directory from one location to another.
     *
     * @param  string   $destination The destination path.
     * @param  int|null $flags       Flags may be provided which will affect the behavior of some methods.
     *                               A list of the flags can found under FilesystemIterator predefined constants.
     *                               https://www.php.net/manual/en/class.filesystemiterator.php#filesystemiterator.constants
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function copy(string $destination, ?int $flags = null): bool
    {
        if (! (new Directory($this->path))->isDirectory()) {
            return false;
        }

        if (! (new Directory($destination))->isDirectory()) {
            (new Directory($destination))->create(0777);
        }

        $flags = $flags ?: FilesystemIterator::SKIP_DOTS;

        foreach (new FilesystemIterator($this->path, $flags) as $item) {
            $target = $destination . '/' . $item->getBasename();

            if ($item->isDir()) {
                $path = $item->getPathname();

                if (! (new Directory($this->path))->copy($target, $flags)) {
                    return false;
                }
            } else {
                if (! (new File($item->getPathname()))->copy($target)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks the existence of directory and returns false if any of them is missing.
     *
     * @return bool Returns true or false if any of them is missing.
     */
    public function exists(): bool
    {
        if (! file_exists($this->path)) {
            return false;
        }

        return true;
    }

    /**
     * Gets size of a given directory in bytes.
     *
     * @return int Returns the size of the directory in bytes.
     */
    public function size(): int
    {
        $size = 0;

        $flags = FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS;

        $dirIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path, $flags));

        foreach ($dirIterator as $splFileInfo) {
            if (! $splFileInfo->isFile()) {
                continue;
            }

            $size += $splFileInfo->getSize();
        }

        return $size;
    }

    /**
     * Determine if the given path is a directory.
     *
     * @return bool Returns TRUE if the given path exists and is a directory, FALSE otherwise.
     */
    public function isDirectory(): bool
    {
        return is_dir($this->path);
    }

    /**
     * Return current path.
     *
     * @return string|null Current path
     */
    public function path(): ?string
    {
        return $this->path;
    }
}
