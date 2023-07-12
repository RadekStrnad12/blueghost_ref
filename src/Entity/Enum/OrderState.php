<?php
namespace App\Entity\Enum;

use App\Repository\Enum\OrderStateRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'enum_order_state')]
#[ORM\Entity(repositoryClass: OrderStateRepository::class)]
class OrderState
{
    public const ORDER_STATE_NEW = 1;
    public const ORDER_STATE_IN_PROCESS = 2;
    public const ORDER_STATE_DELIVERING = 3;
    public const ORDER_STATE_FINISHED = 4;

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

    #[Gedmo\Locale]
    private ?string $locale = null;

    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->name;
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
}