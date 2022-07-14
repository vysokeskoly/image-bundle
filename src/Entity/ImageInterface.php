<?php declare(strict_types=1);

namespace VysokeSkoly\ImageBundle\Entity;

interface ImageInterface
{
    public function getId(): string;

    public function getKey(): string;

    public function getMimetype(): string;

    public function getContent(): string;

    public function setContent(string $content): void;

    public function getETag(): string;
}
