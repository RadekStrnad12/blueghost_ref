<?php
namespace App\Entity\Orders;

use App\Entity\Enum\Delivery;
use App\Entity\Enum\PaymentMethod;
use App\Entity\Security\User;
use App\Entity\Team\Team;
use App\Repository\Orders\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'orders')]
#[ORM\Entity(repositoryClass: OrderRepository::class)]
class Order
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $uuid = null;

    #[ORM\ManyToOne(inversedBy: "orders")]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Delivery $delivery = null;

    #[ORM\ManyToOne]
    private ?PaymentMethod $paymentMethod = null;

    #[Assert\NotBlank(message: "E-mail musí být vyplněn.")]
    #[Assert\Email(message: "E-mailová adresa není správně.")]
    #[ORM\Column(length: 150)]
    private string $email = '';

    #[Assert\NotBlank(message: "Telefon musí být vyplněn.")]
    #[ORM\Column(length: 150)]
    private string $phone = '';

    #[Assert\NotBlank(message: "Jméno musí být vyplněno.")]
    #[ORM\Column(length: 150)]
    private string $billing_name = '';

    #[Assert\NotBlank(message: "Příjmení musí být vyplněno.")]
    #[ORM\Column(length: 150)]
    private string $billing_surname = '';

    #[Assert\NotBlank(message: "Ulice musí být vyplněna.")]
    #[ORM\Column(length: 200)]
    private string $billing_street = '';

    #[Assert\NotBlank(message: "Číslo domu musí být vyplněno.")]
    #[ORM\Column(length: 30)]
    private string $billing_house_number = '';

    #[Assert\NotBlank(message: "Město musí být vyplněno.")]
    #[ORM\Column(length: 200)]
    private string $billing_town = '';

    #[Assert\NotBlank(message: "PSČ musí být vyplněno.")]
    #[ORM\Column(length: 10)]
    private string $billing_post_code = '';

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $delivery_name = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $delivery_surname = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $delivery_street = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $delivery_house_number = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $delivery_town = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $delivery_post_code = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column]
    private int $price = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $payedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $stornedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $zasilkovnaId = null;

    #[ORM\Column(nullable: true)]
    private ?int $zasilkovnaDeliveryPlace = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $zasilkovnaDeliveryPlaceText = null;

    #[ORM\Column(nullable: true)]
    private ?int $fakturoidId = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $fakturoidInvoiceNumber = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $fakturoidInvoiceUrl = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fakturoidSentAt = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $stripe_payment_intent = null;

    #[ORM\OneToMany(mappedBy: "order", targetEntity: OrderItem::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private ?Collection $items = null;

    #[ORM\OneToMany(mappedBy: "order", targetEntity: OrderState::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private ?Collection $states = null;

    #[ORM\OneToMany(mappedBy: "order", targetEntity: Team::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private ?Collection $team = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->items = new ArrayCollection();
        $this->states = new ArrayCollection();
        $this->team = new ArrayCollection();
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function setDeliveryInfo()
    {
        if ($this->delivery_name == null)
        {
            $this->delivery_name = $this->billing_name;
        }

        if ($this->delivery_surname == null)
        {
            $this->delivery_surname = $this->billing_surname;
        }

        if ($this->delivery_street == null)
        {
            $this->delivery_street = $this->billing_street;
        }

        if ($this->delivery_house_number == null)
        {
            $this->delivery_house_number = $this->billing_house_number;
        }

        if ($this->delivery_town == null)
        {
            $this->delivery_town = $this->billing_town;
        }

        if ($this->delivery_post_code == null)
        {
            $this->delivery_post_code = $this->billing_post_code;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getBillingName(): ?string
    {
        return $this->billing_name;
    }

    public function setBillingName(string $billing_name): self
    {
        $this->billing_name = $billing_name;

        return $this;
    }

    public function getBillingSurname(): ?string
    {
        return $this->billing_surname;
    }

    public function setBillingSurname(string $billing_surname): self
    {
        $this->billing_surname = $billing_surname;

        return $this;
    }

    public function getBillingStreet(): ?string
    {
        return $this->billing_street;
    }

    public function setBillingStreet(string $billing_street): self
    {
        $this->billing_street = $billing_street;

        return $this;
    }

    public function getBillingHouseNumber(): ?string
    {
        return $this->billing_house_number;
    }

    public function setBillingHouseNumber(string $billing_house_number): self
    {
        $this->billing_house_number = $billing_house_number;

        return $this;
    }

    public function getBillingTown(): ?string
    {
        return $this->billing_town;
    }

    public function setBillingTown(string $billing_town): self
    {
        $this->billing_town = $billing_town;

        return $this;
    }

    public function getBillingPostCode(): ?string
    {
        return $this->billing_post_code;
    }

    public function setBillingPostCode(string $billing_post_code): self
    {
        $this->billing_post_code = $billing_post_code;

        return $this;
    }

    public function getDeliveryName(): ?string
    {
        return $this->delivery_name;
    }

    public function setDeliveryName(?string $delivery_name): self
    {
        $this->delivery_name = $delivery_name;

        return $this;
    }

    public function getDeliverySurname(): ?string
    {
        return $this->delivery_surname;
    }

    public function setDeliverySurname(?string $delivery_surname): self
    {
        $this->delivery_surname = $delivery_surname;

        return $this;
    }

    public function getDeliveryStreet(): ?string
    {
        return $this->delivery_street;
    }

    public function setDeliveryStreet(?string $delivery_street): self
    {
        $this->delivery_street = $delivery_street;

        return $this;
    }

    public function getDeliveryHouseNumber(): ?string
    {
        return $this->delivery_house_number;
    }

    public function setDeliveryHouseNumber(?string $delivery_house_number): self
    {
        $this->delivery_house_number = $delivery_house_number;

        return $this;
    }

    public function getDeliveryTown(): ?string
    {
        return $this->delivery_town;
    }

    public function setDeliveryTown(?string $delivery_town): self
    {
        $this->delivery_town = $delivery_town;

        return $this;
    }

    public function getDeliveryPostCode(): ?string
    {
        return $this->delivery_post_code;
    }

    public function setDeliveryPostCode(?string $delivery_post_code): self
    {
        $this->delivery_post_code = $delivery_post_code;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPayedAt(): ?\DateTimeInterface
    {
        return $this->payedAt;
    }

    public function setPayedAt(?\DateTimeInterface $payedAt): self
    {
        $this->payedAt = $payedAt;

        return $this;
    }

    public function getStornedAt(): ?\DateTimeInterface
    {
        return $this->stornedAt;
    }

    public function setStornedAt(?\DateTimeInterface $stornedAt): self
    {
        $this->stornedAt = $stornedAt;

        return $this;
    }

    public function getZasilkovnaId(): ?int
    {
        return $this->zasilkovnaId;
    }

    public function setZasilkovnaId(?int $zasilkovnaId): self
    {
        $this->zasilkovnaId = $zasilkovnaId;

        return $this;
    }

    public function getZasilkovnaDeliveryPlace(): ?int
    {
        return $this->zasilkovnaDeliveryPlace;
    }

    public function setZasilkovnaDeliveryPlace(?int $zasilkovnaDeliveryPlace): self
    {
        $this->zasilkovnaDeliveryPlace = $zasilkovnaDeliveryPlace;

        return $this;
    }

    public function getFakturoidId(): ?int
    {
        return $this->fakturoidId;
    }

    public function setFakturoidId(?int $fakturoidId): self
    {
        $this->fakturoidId = $fakturoidId;

        return $this;
    }

    public function getFakturoidInvoiceNumber(): ?string
    {
        return $this->fakturoidInvoiceNumber;
    }

    public function setFakturoidInvoiceNumber(?string $fakturoidInvoiceNumber): self
    {
        $this->fakturoidInvoiceNumber = $fakturoidInvoiceNumber;

        return $this;
    }

    public function getFakturoidInvoiceUrl(): ?string
    {
        return $this->fakturoidInvoiceUrl;
    }

    public function setFakturoidInvoiceUrl(?string $fakturoidInvoiceUrl): self
    {
        $this->fakturoidInvoiceUrl = $fakturoidInvoiceUrl;

        return $this;
    }

    public function getFakturoidSentAt(): ?\DateTimeInterface
    {
        return $this->fakturoidSentAt;
    }

    public function setFakturoidSentAt(?\DateTimeInterface $fakturoidSentAt): self
    {
        $this->fakturoidSentAt = $fakturoidSentAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        if ($user !== null)
        {
            $this->setBillingName($this->getUser()->getName());
            $this->setBillingSurname($this->getUser()->getSurname());
            $this->setEmail($this->getUser()->getEmail());
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeItem(OrderItem $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(?Delivery $delivery): self
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getZasilkovnaDeliveryPlaceText(): ?string
    {
        return $this->zasilkovnaDeliveryPlaceText;
    }

    public function setZasilkovnaDeliveryPlaceText(?string $zasilkovnaDeliveryPlaceText): self
    {
        $this->zasilkovnaDeliveryPlaceText = $zasilkovnaDeliveryPlaceText;

        return $this;
    }

    public function getStripePaymentIntent(): ?string
    {
        return $this->stripe_payment_intent;
    }

    public function setStripePaymentIntent(?string $stripe_payment_intent): void
    {
        $this->stripe_payment_intent = $stripe_payment_intent;
    }

    /**
     * @return Collection<int, OrderState>
     */
    public function getStates(): Collection
    {
        return $this->states;
    }

    public function addState(OrderState $state): static
    {
        if (!$this->states->contains($state)) {
            $this->states->add($state);
            $state->setOrder($this);
        }

        return $this;
    }

    public function removeState(OrderState $state): static
    {
        if ($this->states->removeElement($state)) {
            // set the owning side to null (unless already changed)
            if ($state->getOrder() === $this) {
                $state->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeam(): Collection
    {
        return $this->team;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->team->contains($team)) {
            $this->team->add($team);
            $team->setOrder($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): static
    {
        if ($this->team->removeElement($team)) {
            // set the owning side to null (unless already changed)
            if ($team->getOrder() === $this) {
                $team->setOrder(null);
            }
        }

        return $this;
    }
}