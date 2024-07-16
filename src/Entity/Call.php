<?php

namespace App\Entity;

use App\Repository\CallRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CallRepository::class)]
#[ORM\Table(name: '`call`')]
class Call
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'calls')]
    #[ORM\JoinColumn(nullable: false)]
    private Proxy $proxy;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $started;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $finished = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $calledUrl;

    #[ORM\Column(nullable: true)]
    private ?bool $success = null;

    public function __construct()
    {
        $this->started = new DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Proxy
     */
    public function getProxy(): Proxy
    {
        return $this->proxy;
    }

    /**
     * @param Proxy $proxy
     */
    public function setProxy(Proxy $proxy): void
    {
        $this->proxy = $proxy;
    }

    /**
     * @return DateTimeInterface
     */
    public function getStarted(): DateTimeInterface
    {
        return $this->started;
    }

    /**
     * @param DateTimeInterface $started
     */
    public function setStarted(DateTimeInterface $started): void
    {
        $this->started = $started;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getFinished(): ?DateTimeInterface
    {
        return $this->finished;
    }

    /**
     * @param DateTimeInterface|null $finished
     */
    public function setFinished(?DateTimeInterface $finished): void
    {
        $this->finished = $finished;
    }

    /**
     * @return string
     */
    public function getCalledUrl(): string
    {
        return $this->calledUrl;
    }

    /**
     * @param string $calledUrl
     */
    public function setCalledUrl(string $calledUrl): void
    {
        $this->calledUrl = $calledUrl;
    }

    /**
     * @return bool|null
     */
    public function getSuccess(): ?bool
    {
        return $this->success;
    }

    /**
     * @param bool|null $success
     */
    public function setSuccess(?bool $success): void
    {
        $this->success = $success;
    }

}
