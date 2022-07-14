<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Storage;

interface StorageInterface
{
    /**
     * @return bool|mixed|null
     */
    public function read(string $key);

    /**
     * @param resource $resource
     */
    public function write(string $key, $resource, int $size): void;

    public function remove(string $key): void;

    public function exists(string $key): bool;
}
