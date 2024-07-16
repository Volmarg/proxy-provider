<?php

namespace App\Entity;

use App\Repository\ProxyRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProxyRepository::class)]
#[ORM\UniqueConstraint("uniqProxy", ["ip", "port"])]
class Proxy
{
    public const USAGE_GENERIC = "GENERIC";
    public const USAGE_SERP = "SERP";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private bool $rotating;

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column(type: Types::TEXT)]
    private string $info = '';

    #[ORM\Column(type: Types::STRING)]
    private string $internalId;

    #[ORM\Column(type: Types::STRING)]
    private string $usage;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $targetCountryIsoCode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $lastUsage = null;

    #[ORM\Column(length: 255)]
    private string $ip;

    #[ORM\Column]
    private int $port;

    #[ORM\Column(length: 255)]
    private string $provider;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'proxy', targetEntity: Call::class, orphanRemoval: true)]
    private Collection $calls;

    public function __construct()
    {
        $this->calls = new ArrayCollection();
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
     * @return bool
     */
    public function isRotating(): bool
    {
        return $this->rotating;
    }

    /**
     * @param bool $rotating
     */
    public function setRotating(bool $rotating): void
    {
        $this->rotating = $rotating;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getLastUsage(): ?DateTimeInterface
    {
        return $this->lastUsage;
    }

    /**
     * @param DateTimeInterface|null $lastUsage
     */
    public function setLastUsage(?DateTimeInterface $lastUsage): void
    {
        $this->lastUsage = $lastUsage;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return Collection<int, Call>
     */
    public function getCalls(): Collection
    {
        return $this->calls;
    }

    public function addCall(Call $call): static
    {
        if (!$this->calls->contains($call)) {
            $this->calls->add($call);
            $call->setProxy($this);
        }

        return $this;
    }

    public function removeCall(Call $call): static
    {
        if ($this->calls->removeElement($call)) {
            // set the owning side to null (unless already changed)
            if ($call->getProxy() === $this) {
                $call->setProxy(null);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return string
     */
    public function getInfo(): string
    {
        return $this->info;
    }

    /**
     * @param string $info
     */
    public function setInfo(string $info): void
    {
        $this->info = $info;
    }

    /**
     * @return string
     */
    public function getInternalId(): string
    {
        return $this->internalId;
    }

    /**
     * @param string $internalId
     */
    public function setInternalId(string $internalId): void
    {
        $this->internalId = $internalId;
    }

    /**
     * @return string|null
     */
    public function getUsage(): ?string
    {
        return $this->usage;
    }

    /**
     * @param string|null $usage
     */
    public function setUsage(?string $usage): void
    {
        $this->usage = $usage;
    }

    /**
     * @return string|null
     */
    public function getTargetCountryIsoCode(): ?string
    {
        return $this->targetCountryIsoCode;
    }

    /**
     * @param string|null $targetCountryIsoCode
     */
    public function setTargetCountryIsoCode(?string $targetCountryIsoCode): void
    {
        $this->targetCountryIsoCode = $targetCountryIsoCode;
    }

}
