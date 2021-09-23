<?php

namespace Adeliom\EasyRedirectBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @UniqueEntity(
 *     fields="source",
 *     errorPath="source",
 *     message="easy_redirect.source.unique"
 * )
 * @ORM\MappedSuperclass()
 */
class Redirect
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @Groups("main")
     * @ORM\Column(name="source", type="string", length="255", unique=true)
     * @Assert\NotBlank(message="easy_redirect.source.blank")
     */
    protected $source;

    /**
     * @var string
     * @Groups("main")
     * @ORM\Column(name="destination", type="string", length="255")
     * @Assert\NotBlank(message="easy_redirect.destination.blank")
     */
    protected $destination;

    /**
     * @var string
     * @Groups("main")
     * @ORM\Column(name="status", type="string", length="10")
     * @Assert\Type("string")
     */
    protected $status;

    /**
     * @var int
     * @Groups("main")
     * @ORM\Column(name="count", type="integer")
     */
    protected $count = 0;

    /**
     * @var \DateTime
     * @Groups("main")
     * @ORM\Column(name="last_accessed", type="datetime", nullable=true)
     */
    protected $lastAccessed = null;

    /**
     * @param string $source
     * @param string $destination
     * @param bool   $permanent
     */
    public function __construct($source = null, $destination = null, $status = 301)
    {
        if ($source){
            $this->setSource($source);
        }
        if ($destination){
            $this->setDestination($destination);
        }
        $this->setStatus($status);
    }

    /**
     * @param string $destination
     * @param bool   $permanent
     *
     * @return static
     */
    public static function createFromNotFound(NotFound $notFound, $destination, $status = 301)
    {
        return new static($notFound->getPath(), $destination, $status);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $source = \trim($source);
        $source = !empty($source) ? $source : null;

        if (null !== $source) {
            $source = $this->createAbsoluteUri($source);
        }

        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     */
    public function setDestination($destination)
    {
        $destination = \trim($destination);
        $destination = !empty($destination) ? $destination : null;

        if (null !== $destination && null === \parse_url($destination, \PHP_URL_SCHEME)) {
            $destination = $this->createAbsoluteUri($destination, true);
        }

        $this->destination = $destination;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $amount
     */
    public function increaseCount($amount = 1)
    {
        $this->count += $amount;
    }

    /**
     * @return \DateTime
     */
    public function getLastAccessed()
    {
        return $this->lastAccessed;
    }

    /**
     * @param \DateTime $time
     */
    public function updateLastAccessed(\DateTime $time = null)
    {
        if (null === $time) {
            $time = new \DateTime('now');
        }

        $this->lastAccessed = $time;
    }

    /**
     * @param string $path
     * @param bool   $allowQueryString
     *
     * @return string
     */
    protected function createAbsoluteUri($path, $allowQueryString = false)
    {
        $value = '/'.\ltrim(\parse_url($path, \PHP_URL_PATH), '/');

        if ($allowQueryString && $query = \parse_url($path, \PHP_URL_QUERY)) {
            $value .= '?'.$query;
        }

        return $value;
    }
}
