<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CardRepository::class)
 */
class Card
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="card", orphanRemoval=true)
     */
    private $products;

    /**
     * @ORM\OneToOne(targetEntity=Establishment::class, inversedBy="card")
     * @ORM\JoinColumn(nullable=false)
     */
    private $establishment;

    /**
     * @ORM\OneToMany(targetEntity=Section::class, mappedBy="card", orphanRemoval=true)
     */
    private $sections;

    /**
     * @ORM\OneToMany(targetEntity=Sauce::class, mappedBy="Card")
     */
    private $sauces;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->sections = new ArrayCollection();
        $this->sauces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $product->setCard($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCard() === $this) {
                $product->setCard(null);
            }
        }

        return $this;
    }

    public function getEstablishment(): ?Establishment
    {
        return $this->establishment;
    }

    public function setEstablishment(?Establishment $establishment): self
    {
        $this->establishment = $establishment;

        return $this;
    }

    /**
     * @return Collection<int, Section>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setCard($this);
        }

        return $this;
    }

    public function removeSection(Section $section): self
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getCard() === $this) {
                $section->setCard(null);
            }
        }

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
            $sauce->setCard($this);
        }

        return $this;
    }

    public function removeSauce(Sauce $sauce): self
    {
        if ($this->sauces->removeElement($sauce)) {
            // set the owning side to null (unless already changed)
            if ($sauce->getCard() === $this) {
                $sauce->setCard(null);
            }
        }

        return $this;
    }
}
