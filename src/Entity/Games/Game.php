<?php
namespace App\Entity\Games;

use App\Entity\Enum\GameDifficulty;
use App\Entity\Enum\GameType;
use App\Repository\Games\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'games')]
#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?GameType $type = null;

    #[ORM\ManyToOne]
    private ?GameDifficulty $difficulty = null;

    #[ORM\Column(length: 150)]
    #[Gedmo\Translatable]
    private string $name = '';

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $nameFaktura = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Translatable]
    private string $shortDescription = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Translatable]
    private string $longDescription = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $story = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $aboutProduct = null;

    #[ORM\Column(length: 150)]
    #[Gedmo\Translatable]
    private string $price = '';

    #[ORM\Column(length: 150, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $nonDiscountedPrice = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $discountText = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $town = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $startLocation = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $equipment = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $routeLength = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $timeLength = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Translatable]
    private ?int $teamSizeMin = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Translatable]
    private ?int $teamSizeMax = null;

    #[ORM\Column]
    private bool $createTeamAutomatically = false;

    #[ORM\Column(length: 150, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $image = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $age = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $alert_type = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $alert_title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $alert_message = null;

    #[ORM\Column(length: 190)]
    #[Gedmo\Slug(fields: ["name"], updatable: false, unique: true)]
    private ?string $slug = null;

    #[ORM\Column]
    private bool $showItem = false;

    #[ORM\Column]
    private bool $preparing = false;

    #[Gedmo\Locale]
    private ?string $locale = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $stripe_id = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $stripe_price_id = null;

    #[ORM\OneToMany(mappedBy: "game", targetEntity: GameRating::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private ?Collection $ratings = null;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
    }

    public function countRating()
    {
        if ($this->ratings->count() == 0)
        {
            return 0;
        }

        $count = 0;
        foreach ($this->ratings as $rating)
        {
            $count += $rating->getRating();
        }

        return round($count / $this->ratings->count(), 1);
    }

    public function getRatingsWithComment()
    {
        $ratings = [];
        foreach ($this->ratings as $rating)
        {
            if ($rating->getComment() == "") continue;

            $ratings[] = $rating;
        }

        return $ratings;
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

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function setLongDescription(?string $longDescription): self
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    public function getStory(): ?string
    {
        return $this->story;
    }

    public function setStory(?string $story): self
    {
        $this->story = $story;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(?string $town): self
    {
        $this->town = $town;

        return $this;
    }

    public function getStartLocation(): ?string
    {
        return $this->startLocation;
    }

    public function setStartLocation(?string $startLocation): self
    {
        $this->startLocation = $startLocation;

        return $this;
    }

    public function getEquipment(): ?string
    {
        return $this->equipment;
    }

    public function setEquipment(?string $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getRouteLength(): ?string
    {
        return $this->routeLength;
    }

    public function setRouteLength(?string $routeLength): self
    {
        $this->routeLength = $routeLength;

        return $this;
    }

    public function getTimeLength(): ?string
    {
        return $this->timeLength;
    }

    public function setTimeLength(?string $timeLength): self
    {
        $this->timeLength = $timeLength;

        return $this;
    }

    public function getTeamSizeMin(): ?int
    {
        return $this->teamSizeMin;
    }

    public function setTeamSizeMin(int $teamSizeMin): self
    {
        $this->teamSizeMin = $teamSizeMin;

        return $this;
    }

    public function getTeamSizeMax(): ?int
    {
        return $this->teamSizeMax;
    }

    public function setTeamSizeMax(int $teamSizeMax): self
    {
        $this->teamSizeMax = $teamSizeMax;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDifficulty(): ?GameDifficulty
    {
        return $this->difficulty;
    }

    public function setDifficulty(?GameDifficulty $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    /**
     * @return Collection<int, GameRating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(GameRating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setGame($this);
        }

        return $this;
    }

    public function removeRating(GameRating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getGame() === $this) {
                $rating->setGame(null);
            }
        }

        return $this;
    }

    public function getNonDiscountedPrice(): ?string
    {
        return $this->nonDiscountedPrice;
    }

    public function setNonDiscountedPrice(?string $nonDiscountedPrice): self
    {
        $this->nonDiscountedPrice = $nonDiscountedPrice;

        return $this;
    }

    public function getType(): ?GameType
    {
        return $this->type;
    }

    public function setType(?GameType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function isShowItem(): ?bool
    {
        return $this->showItem;
    }

    public function setShowItem(bool $showItem): self
    {
        $this->showItem = $showItem;

        return $this;
    }

    public function getAboutProduct(): ?string
    {
        return $this->aboutProduct;
    }

    public function setAboutProduct(?string $aboutProduct): self
    {
        $this->aboutProduct = $aboutProduct;

        return $this;
    }

    public function getDiscountText(): ?string
    {
        return $this->discountText;
    }

    public function setDiscountText(?string $discountText): self
    {
        $this->discountText = $discountText;

        return $this;
    }

    public function isPreparing(): ?bool
    {
        return $this->preparing;
    }

    public function setPreparing(bool $preparing): self
    {
        $this->preparing = $preparing;

        return $this;
    }

    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(?string $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getAlertType(): ?string
    {
        return $this->alert_type;
    }

    public function setAlertType(?string $alert_type): self
    {
        $this->alert_type = $alert_type;

        return $this;
    }

    public function getAlertTitle(): ?string
    {
        return $this->alert_title;
    }

    public function setAlertTitle(?string $alert_title): self
    {
        $this->alert_title = $alert_title;

        return $this;
    }

    public function getAlertMessage(): ?string
    {
        return $this->alert_message;
    }

    public function setAlertMessage(?string $alert_message): self
    {
        $this->alert_message = $alert_message;

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

    public function getNameFaktura(): ?string
    {
        return $this->nameFaktura;
    }

    public function setNameFaktura(?string $nameFaktura): void
    {
        $this->nameFaktura = $nameFaktura;
    }

    public function getStripePriceId(): ?string
    {
        return $this->stripe_price_id;
    }

    public function setStripePriceId(?string $stripe_price_id): void
    {
        $this->stripe_price_id = $stripe_price_id;
    }

    public function isCreateTeamAutomatically(): bool
    {
        return $this->createTeamAutomatically;
    }

    public function setCreateTeamAutomatically(bool $createTeamAutomatically): void
    {
        $this->createTeamAutomatically = $createTeamAutomatically;
    }
}