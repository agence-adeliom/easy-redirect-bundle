<?php

namespace Adeliom\EasyRedirectBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[UniqueEntity('source')]
#[ORM\MappedSuperclass]
class NotFound
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private mixed $id;

    #[Groups('main')]
    #[ORM\Column(name: 'path', type: 'string', length: 500)]
    protected string $path;

    #[Groups('main')]
    #[ORM\Column(name: 'full_url', type: 'string', length: 500)]
    protected ?string $fullUrl;

    #[Groups('main')]
    #[ORM\Column(name: 'timestamp', type: 'datetime')]
    protected ?\DateTimeInterface $timestamp;

    #[Groups('main')]
    #[ORM\Column(name: 'referer', type: 'string', length: 1000, nullable: true)]
    protected ?string $referer;

    public function __construct(string $path, string $fullUrl, ?string $referer, ?\DateTimeInterface $timestamp)
    {
        if (null === $timestamp) {
            $timestamp = new \DateTime('now');
        }

        $path = \trim($path);
        $path = !empty($path) ? $path : null;

        if (null !== $path) {
            $path = '/'.\ltrim(\parse_url($path, \PHP_URL_PATH), '/');
        }

        $this->path = $path;
        $this->fullUrl = $fullUrl;
        $this->referer = $referer;
        $this->timestamp = $timestamp;
    }

    public function getId(): mixed
    {
        return $this->id;
    }
    public function getPath(): string
    {
        return $this->path;
    }
    public function getFullUrl(): string
    {
        return $this->fullUrl;
    }
    public function getTimestamp(): \DateTimeInterface
    {
        return $this->timestamp;
    }
    public function getReferer(): ?string
    {
        return $this->referer;
    }
}
