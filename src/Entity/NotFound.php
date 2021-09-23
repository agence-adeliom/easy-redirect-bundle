<?php

namespace Adeliom\EasyRedirectBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @UniqueEntity("source")
 * @ORM\MappedSuperclass()
 */
class NotFound
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
     * @ORM\Column(name="path", type="string", length="500")
     */
    protected $path;

    /**
     * @var string
     * @Groups("main")
     * @ORM\Column(name="full_url", type="string", length="500")
     */
    protected $fullUrl;

    /**
     * @var string
     * @Groups("main")
     * @ORM\Column(name="timestamp", type="datetime")
     */
    protected $timestamp;

    /**
     * @var string
     * @Groups("main")
     * @ORM\Column(name="referer", type="string", length="1000", nullable=true)
     */
    protected $referer;

    /**
     * @param string      $path
     * @param string      $fullUrl
     * @param string|null $referer
     */
    public function __construct($path, $fullUrl, $referer = null, \DateTime $timestamp = null)
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
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getFullUrl()
    {
        return $this->fullUrl;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return string|null
     */
    public function getReferer()
    {
        return $this->referer;
    }
}
