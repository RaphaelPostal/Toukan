<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{

    CONST TYPE_BOISSON = 'Boisson';
    CONST TYPE_PLAT = 'Boisson';
    CONST TYPE_MENU = 'Boisson';

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
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\Regex("/\d+(?:[.,]\d{0,2})?/" , message="Le format du prix n'est pas valide")
     */

    private $price;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Card::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     */
    private $card;

    /**
     * @ORM\OneToMany(targetEntity=ProductOrder::class, mappedBy="product")
     */
    private $productOrders;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class, inversedBy="products")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $section;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $ingredients;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $allergens;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $available = true;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sauce_choosable;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_menu;

    /**
     * @ORM\Column(type="boolean")
     */
    private $drink_choosable;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $menu_information;

    public function __construct()
    {
        $this->productOrders = new ArrayCollection();
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

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price): self
    {
        $price = str_replace(',', '.', $price);
        //test if last char of price is €
        if (str_contains($price, '€')) {
            //test if first char of price is €
            if (str_ends_with($price, '€')) {
                $price = str_replace('€', '', $price);
            } else {
                $price = str_replace('€', '.', $price);
            }
        }

        $this->price = $price;

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
            $productOrder->setProduct($this);
        }

        return $this;
    }

    public function removeProductOrder(ProductOrder $productOrder): self
    {
        if ($this->productOrders->removeElement($productOrder)) {
            // set the owning side to null (unless already changed)
            if ($productOrder->getProduct() === $this) {
                $productOrder->setProduct(null);
            }
        }

        return $this;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getIngredients(): ?string
    {
        return $this->ingredients;
    }

    public function setIngredients(?string $ingredients): self
    {
        $this->ingredients = $ingredients;

        return $this;
    }

    public function getAllergens(): ?string
    {
        return $this->allergens;
    }

    public function setAllergens(string $allergens): self
    {
        $this->allergens = $allergens;

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

    public function getAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(?bool $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getSauceChoosable(): ?bool
    {
        return $this->sauce_choosable;
    }

    public function setSauceChoosable(?bool $sauce_choosable): self
    {
        $this->sauce_choosable = $sauce_choosable;

        return $this;
    }

    public function getIsMenu(): ?bool
    {
        return $this->is_menu;
    }

    public function setIsMenu(?bool $is_menu): self
    {
        $this->is_menu = $is_menu;

        return $this;
    }

    public function getDrinkChoosable(): ?bool
    {
        return $this->drink_choosable;
    }

    public function setDrinkChoosable(bool $drink_choosable): self
    {
        $this->drink_choosable = $drink_choosable;

        return $this;
    }

    public function getMenuInformation(): ?string
    {
        return $this->menu_information;
    }

    public function setMenuInformation(?string $menu_information): self
    {
        $this->menu_information = $menu_information;

        return $this;
    }
}
