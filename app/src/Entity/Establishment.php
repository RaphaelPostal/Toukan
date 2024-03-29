<?php

namespace App\Entity;

use App\Repository\EstablishmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EstablishmentRepository::class)
 */
class Establishment
{
    public \Doctrine\Common\Collections\ArrayCollection $cards;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="array", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="establishment")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity=Card::class, mappedBy="establishment", fetch="EAGER")
     */
    private $card;

    /**
     * @ORM\OneToMany(targetEntity=Table::class, mappedBy="establishment")
     */
    private $tables;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $custom_color = '#F49B22';

    /**
     * @ORM\Column(type="integer", options={"default": 1})
     */
    private $qrCodeTemplate;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="establishment", orphanRemoval=true)
     */
    private $orders;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->tables = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->qrCodeTemplate = 1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAddress(): ?array
    {
        return $this->address;
    }

    public function setAddress(?array $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function getTables()
    {
        return $this->tables;
    }

    public function addTable(Table $table): self
    {
        if (!$this->tables->contains($table)) {
            $this->tables[] = $table;
            $table->setEstablishment($this);
        }

        return $this;
    }

    public function removeTable(Table $table): self
    {
        // set the owning side to null (unless already changed)
        if ($this->tables->removeElement($table) && $table->getEstablishment() === $this) {
            $table->setEstablishment(null);
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCustomColor(): ?string
    {
        return $this->custom_color;
    }

    public function setCustomColor(?string $custom_color): self
    {
        $this->custom_color = $custom_color;

        return $this;
    }

    public function getQrCodeTemplate(): ?int
    {
        return $this->qrCodeTemplate;
    }

    public function setQrCodeTemplate(int $qrCodeTemplate): self
    {
        $this->qrCodeTemplate = $qrCodeTemplate;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setEstablishment($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getEstablishment() === $this) {
                $order->setEstablishment(null);
            }
        }

        return $this;
    }
}
