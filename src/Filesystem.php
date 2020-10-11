<?php

declare(strict_types=1);

namespace Atomastic\Filesystem;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_executable;
use function is_file;
use function is_readable;
use function is_writable;
use function md5_file;
use function strpos;
use function unlink;

use const FILE_APPEND;
use const LOCK_EX;

class Filesystem
{
    /**
     * Determine if the given path is a regular file.
     *
     * @param  string $path Path to the file.
     *
     * @return bool Returns TRUE if the filename exists and is a regular file, FALSE otherwise.
     */
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param  string $path Path to check.
     *
     * @return bool Returns TRUE if the given path exists and is a directory, FALSE otherwise.
     */
    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Determine if the given path is readable.
     *
     * @param  string $path Path to check.
     *
     * @return bool Returns TRUE if the given path exists and is readable, FALSE otherwise.
     */
    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    /**
     * Determine if the given path is writable.
     *
     * @param  string $path Path to check.
     *
     * @return bool Returns TRUE if the given path exists and is writable, FALSE otherwise.
     */
    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * Determine if the given path is a stream path.
     *
     * @param  string $path Path to check.
     *
     * @return bool Returns TRUE if the given path is stream path, FALSE otherwise.
     */
    public function isStream(string $path): bool
    {
        return strpos($path, '://') !== false;
    }

    /**
     * Returns true if the File is executable.
     *
     * @param  string $path Path to check.
     *
     * @return bool Returns TRUE if the given path is stream path, FALSE otherwise.
     */
    public function isExecutable(string $path): bool
    {
        return is_executable($path);
    }

    /**
     * Checks the existence of files or directories and returns false if any of them is missing.
     *
     * @param string|string[] $paths A path, or an array of paths to check.
     *
     * @return bool Returns true or false if any of them is missing.
     */
    public function exists($paths): bool
    {
        foreach ((array) $paths as $path) {
            if (! file_exists($path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Write the contents of a file.
     *
     * @param  string $path Path to the file where to write the data.
     * @param  string $data The data to write.
     * @param  bool   $lock Acquire an exclusive lock on the file while proceeding to the writing.
     *
     * @return int|bool Returns the number of bytes that were written to the file, or FALSE on failure.
     */
    public function put(string $path, string $data, bool $lock = false)
    {
        return file_put_contents($path, $data, $lock ? LOCK_EX : 0);
    }

    /**
     * Delete the file at a given path.
     *
     * @param string|string[] $paths A path, or an array of paths to delete.
     *
     * @return bool Returns true or false if any of them is failure.
     */
    public function delete($paths): bool
    {
        $result = true;

        foreach ((array) $paths as $path) {
            try {
                if (! @unlink($path)) {
                    $result = false;
                }
            } catch (IOException $e) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Get the MD5 hash of the file at the given path.
     *
     * @param  string $path The path.
     *
     * @return string Returns a string on success, FALSE otherwise.
     */
    public function hash(string $path, bool $rawOutput = false): string
    {
        return md5_file($path, $rawOutput);
    }

    /**
     * Get the contents of a file.
     *
     * @param string $path The path to the file.
     *
     * @return string|false The file contents or false on failure.
     */
    public static function get(string $path)
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            return false;
        }

        return $contents;
    }

    /**
     * Prepend to a file.
     *
     * @param  string $path Path to the file where to write the data.
     * @param  string $data The data to write.
     *
     * @return int|bool Returns the number of bytes that were written to the file, or FALSE on failure.
     */
    public function prepend(string $path, string $data)
    {
        if ($this->exists($path)) {
            return $this->put($path, $data . $this->get($path));
        }

        return $this->put($path, $data);
    }

    /**
     * Append to a file.
     *
     * @param  string $path Path to the file where to write the data.
     * @param  string $data The data to write.
     *
     * @return int|bool Returns the number of bytes that were written to the file, or FALSE on failure.
     */
    public function append(string $path, string $data)
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }
}
