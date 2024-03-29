<?php

namespace App\Entity;

use App\Repository\ProductOrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductOrderRepository::class)
 */
class ProductOrder
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="productOrders")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="productOrders")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $orderEntity;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=Sauce::class, inversedBy="ProductOrder")
     */
    private $sauce;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class)
     */
    private $drink;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class)
     */
    private $dessert;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getOrderEntity(): ?Order
    {
        return $this->orderEntity;
    }

    public function setOrderEntity(?Order $orderEntity): self
    {
        $this->orderEntity = $orderEntity;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSauce(): ?Sauce
    {
        return $this->sauce;
    }

    public function setSauce(?Sauce $sauce): self
    {
        $this->sauce = $sauce;

        return $this;
    }

    public function getDrink(): ?Product
    {
        return $this->drink;
    }

    public function setDrink(?Product $drink): self
    {
        $this->drink = $drink;

        return $this;
    }

    public function getDessert(): ?Product
    {
        return $this->dessert;
    }

    public function setDessert(?Product $dessert): self
    {
        $this->dessert = $dessert;

        return $this;
    }
}
