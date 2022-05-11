<?php

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SectionRepository::class)
 */
class Section
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="section", fetch="EAGER")
     */
    private $products;

    /**
     * @ORM\ManyToOne(targetEntity=Card::class, inversedBy="sections")
     * @ORM\JoinColumn(nullable=false)
     */
    private $card;

    /**
     * @ORM\OneToMany(targetEntity=Sauce::class, mappedBy="Section")
     */
    private $sauces;


    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->sauces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setSection($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getSection() === $this) {
                $product->setSection(null);
            }
        }

        return $this;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): self
    {
        $this->card = $card;

        return $this;
    }

    /**
     * @return Collection<int, Sauce>
     */
    public function getSauces(): Collection
    {
        return $this->sauces;
    }

    public function addSauce(Sauce $sauce): self
    {
        if (!$this->sauces->contains($sauce)) {
            $this->sauces[] = $sauce;
            $sauce->setSection($this);
        }

        return $this;
    }

    public function removeSauce(Sauce $sauce): self
    {
        if ($this->sauces->removeElement($sauce)) {
            // set the owning side to null (unless already changed)
            if ($sauce->getSection() === $this) {
                $sauce->setSection(null);
            }
        }

        return $this;
    }
}
