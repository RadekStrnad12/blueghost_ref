<?php
namespace App\Entity\Enum;

use App\Repository\Enum\GameTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'enum_game_type')]
#[ORM\Entity(repositoryClass: GameTypeRepository::class)]
class GameType
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
    private bool $isDigitalContent = false;

    #[Gedmo\Locale]
    private ?string $locale = null;

    public function __construct()
    {
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

    public function isIsDigitalContent(): ?bool
    {
        return $this->isDigitalContent;
    }

    public function setIsDigitalContent(bool $isDigitalContent): self
    {
        $this->isDigitalContent = $isDigitalContent;

        return $this;
    }
}