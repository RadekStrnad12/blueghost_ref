<?php
namespace App\Entity\Orders;

use App\Entity\Security\User;
use App\Repository\Orders\OrderStateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'orders_state')]
#[ORM\Entity(repositoryClass: OrderStateRepository::class)]
class OrderState
{
    #[ORM\Column]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "states")]
    private ?Order $order = null;

    #[ORM\ManyToOne]
    private ?\App\Entity\Enum\OrderState $orderState = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dt = null;

    public function __construct()
    {
        $this->dt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDt(): ?\DateTimeInterface
    {
        return $this->dt;
    }

    public function setDt(?\DateTimeInterface $dt): static
    {
        $this->dt = $dt;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getOrderState(): ?\App\Entity\Enum\OrderState
    {
        return $this->orderState;
    }

    public function setOrderState(?\App\Entity\Enum\OrderState $orderState): static
    {
        $this->orderState = $orderState;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}