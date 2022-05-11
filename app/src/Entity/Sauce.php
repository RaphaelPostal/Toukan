<?php

namespace App\Entity;

use App\Repository\SauceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SauceRepository::class)
 */
class Sauce
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Card::class, inversedBy="sauces")
     */
    private $Card;


    /**
     * @ORM\OneToMany(targetEntity=ProductOrder::class, mappedBy="sauce")
     */
    private $ProductOrder;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class, inversedBy="sauces")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $Section;

    public function __construct()
    {
        $this->ProductOrder = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCard(): ?Card
    {
        return $this->Card;
    }

    public function setCard(?Card $Card): self
    {
        $this->Card = $Card;

        return $this;
    }

    /**
     * @return Collection<int, ProductOrder>
     */
    public function getProductOrder(): Collection
    {
        return $this->ProductOrder;
    }

    public function addProductOrder(ProductOrder $productOrder): self
    {
        if (!$this->ProductOrder->contains($productOrder)) {
            $this->ProductOrder[] = $productOrder;
            $productOrder->setSauce($this);
        }

        return $this;
    }

    public function removeProductOrder(ProductOrder $productOrder): self
    {
        if ($this->ProductOrder->removeElement($productOrder)) {
            // set the owning side to null (unless already changed)
            if ($productOrder->getSauce() === $this) {
                $productOrder->setSauce(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSection(): ?Section
    {
        return $this->Section;
    }

    public function setSection(?Section $Section): self
    {
        $this->Section = $Section;

        return $this;
    }
}
