<?php

namespace Adeliom\EasyRedirectBundle\Entity;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[UniqueEntity(fields: 'source', message: 'easy_redirect.source.unique', errorPath: 'source')]
#[ORM\MappedSuperclass]
class Redirect
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private mixed $id;

    #[Groups('main')]
    #[ORM\Column(name: 'source', type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(message: 'easy_redirect.source.blank')]
    protected string $source;

    #[Groups('main')]
    #[ORM\Column(name: 'destination', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'easy_redirect.destination.blank')]
    protected string $destination;

    #[Groups('main')]
    #[ORM\Column(name: 'status', type: 'string', length: 10)]
    #[Assert\Type('string')]
    protected string $status;

    #[Groups('main')]
    #[ORM\Column(name: 'count', type: 'integer')]
    protected int $count = 0;

    #[Groups('main')]
    #[ORM\Column(name: 'last_accessed', type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $lastAccessed;

    public function __construct(?string $source, ?string $destination, int $status = 301)
    {
        if ($source){
            $this->setSource($source);
        }
        if ($destination){
            $this->setDestination($destination);
        }
        $this->setStatus($status);
    }

    public static function createFromNotFound(NotFound $notFound, string $destination, $status = 301): static
    {
        return new static($notFound->getPath(), $destination, $status);
    }

    public function getId(): mixed
    {
        return $this->id;
    }
    public function getSource(): string
    {
        return $this->source;
    }
    public function setSource(string $source): void
    {
        $source = \trim($source);
        $source = !empty($source) ? $source : null;

        if (null !== $source) {
            $source = $this->createAbsoluteUri($source);
        }

        $this->source = $source;
    }
    public function getDestination(): string
    {
        return $this->destination;
    }
    public function setDestination(string $destination): void
    {
        $destination = \trim($destination);
        $destination = !empty($destination) ? $destination : null;

        if (null !== $destination && null === \parse_url($destination, \PHP_URL_SCHEME)) {
            $destination = $this->createAbsoluteUri($destination, true);
        }

        $this->destination = $destination;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function getCount(): int
    {
        return $this->count;
    }
    public function increaseCount(int $amount = 1): void
    {
        $this->count += $amount;
    }
    public function getLastAccessed(): \DateTimeInterface
    {
        return $this->lastAccessed;
    }
    public function updateLastAccessed(?\DateTimeInterface $time): void
    {
        if (null === $time) {
            $time = new DateTime('now');
        }

        $this->lastAccessed = $time;
    }
    protected function createAbsoluteUri(string $path, bool $allowQueryString = false): string
    {
        $value = '/'.\ltrim(\parse_url($path, \PHP_URL_PATH), '/');

        if ($allowQueryString && $query = \parse_url($path, \PHP_URL_QUERY)) {
            $value .= '?'.$query;
        }

        return $value;
    }
}
