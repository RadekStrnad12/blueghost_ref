<?php
namespace App\Entity\Enum;

use App\Repository\Enum\DeliveryRepository;
use App\Tools\PrintPrice;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'enum_delivery')]
#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
class Delivery
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

    #[ORM\Column]
    private int $price = 0;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $stripe_id = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $stripe_price_id = null;

    #[Gedmo\Locale]
    private ?string $locale = null;

    public function __construct()
    {
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

    public function getStripeId(): ?string
    {
        return $this->stripe_id;
    }

    public function setStripeId(?string $stripe_id): static
    {
        $this->stripe_id = $stripe_id;

        return $this;
    }

    public function getStripePriceId(): ?string
    {
        return $this->stripe_price_id;
    }

    public function setStripePriceId(?string $stripe_price_id): void
    {
        $this->stripe_price_id = $stripe_price_id;
    }
}