<?php
namespace App\Entity\Enum;

use App\Repository\Enum\PaymentMethodRepository;
use App\Tools\PrintPrice;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'enum_payment_method')]
#[ORM\Entity(repositoryClass: PaymentMethodRepository::class)]
class PaymentMethod
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Gedmo\Translatable]
    private string $name = '';

    #[ORM\Column(length: 190)]
    #[Gedmo\Slug(fields: ["name"], updatable: false, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(nullable: true)]
    private ?int $price = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $fakturoid_code = null;

    #[ORM\Column(nullable: true)]
    private ?int $ordering = null;

    #[Gedmo\Locale]
    private ?string $locale = null;

    public function __construct()
    {
    }

    public function getOrdering(): ?int
    {
        return $this->ordering;
    }

    public function setOrdering(?int $ordering): void
    {
        $this->ordering = $ordering;
    }

    public function __toString()
    {
        return $this->name . ($this->price != null ? ' (+' . PrintPrice::printPrice($this->price) . ')' : '');
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getFakturoidCode(): ?string
    {
        return $this->fakturoid_code;
    }

    public function setFakturoidCode(?string $fakturoid_code): static
    {
        $this->fakturoid_code = $fakturoid_code;

        return $this;
    }
}