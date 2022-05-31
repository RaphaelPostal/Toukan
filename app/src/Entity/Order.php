<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    final const STATUS_ORDERING = 'ordering'; // commande non validée par le client
    final const STATUS_IN_PROGRESS = 'in_progress'; // commande validée par le client, en attente de livraison
    final const STATUS_DELIVERED = 'delivered'; // commande servie
    final const STATUS_CANCELED = 'canceled';
    final const STATUS_PAYED = 'payed';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Table::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $establishmentTable;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $customInfos;

    /**
     * @ORM\OneToMany(targetEntity=ProductOrder::class, mappedBy="orderEntity", orphanRemoval=true)
     */
    private $productOrders;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    public function __construct()
    {
        $this->productOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstablishmentTable(): ?Table
    {
        return $this->establishmentTable;
    }

    public function setEstablishmentTable(?Table $establishmentTable): self
    {
        $this->establishmentTable = $establishmentTable;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCustomInfos(): ?string
    {
        return $this->customInfos;
    }

    public function setCustomInfos(?string $customInfos): self
    {
        $this->customInfos = $customInfos;

        return $this;
    }

    /**
     * @return Collection<int, ProductOrder>
     */
    public function getProductOrders(): Collection
    {
        return $this->productOrders;
    }

    public function addProductOrder(ProductOrder $productOrder): self
    {
        if (!$this->productOrders->contains($productOrder)) {
            $this->productOrders[] = $productOrder;
            $productOrder->setOrderEntity($this);
        }

        return $this;
    }

    public function removeProductOrder(ProductOrder $productOrder): self
    {
        // set the owning side to null (unless already changed)
        if ($this->productOrders->removeElement($productOrder) && $productOrder->getOrderEntity() === $this) {
            $productOrder->setOrderEntity(null);
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
