<?php

namespace Adeliom\EasyRedirectBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass]
#[UniqueEntity(fields: ['source', 'host'], message: 'easy_redirect.source.unique', errorPath: 'source')]
class Redirect
{
    /**
     * @var mixed
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private $id;

    #[Groups('main')]
    #[ORM\Column(name: 'source', type: \Doctrine\DBAL\Types\Types::STRING, length: 500)]
    #[Assert\NotBlank(message: 'easy_redirect.source.blank')]
    protected string $source;

    #[Groups('main')]
    #[ORM\Column(name: 'host', type: \Doctrine\DBAL\Types\Types::STRING, length: 255, options: ["default" => ""])]
    protected ?string $host = "";

    #[Groups('main')]
    #[ORM\Column(name: 'destination', type: \Doctrine\DBAL\Types\Types::STRING, length: 500)]
    #[Assert\NotBlank(message: 'easy_redirect.destination.blank')]
    protected string $destination;

    #[Groups('main')]
    #[ORM\Column(name: 'status', type: \Doctrine\DBAL\Types\Types::STRING, length: 10)]
    #[Assert\Type('string')]
    protected string $status;

    #[Groups('main')]
    #[ORM\Column(name: 'count', type: \Doctrine\DBAL\Types\Types::INTEGER)]
    protected int $count = 0;

    #[Groups('main')]
    #[ORM\Column(name: 'last_accessed', type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $lastAccessed = null;

    public function __construct(?string $source = null, ?string $destination = null, ?string $host = "", int $status = 301)
    {
        if ($source) {
            $this->setSource($source);
        }

        if ($destination) {
            $this->setDestination($destination);
        }

        if ($host) {
            $this->setHost($host);
        }

        $this->setStatus($status);
    }

    /**
     * @return $this
     */
    public static function createFromNotFound(NotFound $notFound, string $destination, $status = 301)
    {
        return new static($notFound->getPath(), $destination, $notFound->getHost(), $status);
    }

    /**
     * @return mixed
     */
    public function getId()
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
        $source = empty($source) ? null : $source;

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
        $destination = empty($destination) ? null : $destination;

        if (null !== $destination && null === \parse_url($destination, \PHP_URL_SCHEME)) {
            $destination = $this->createAbsoluteUri($destination, true);
        }

        $this->destination = $destination;
    }

    public function setHost(?string $host = ""): void
    {
        $this->host = $host ?? "";
    }

    public function getHost(): ?string
    {
        return $this->host;
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

    public function updateLastAccessed(?\DateTimeInterface $time = null): void
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
