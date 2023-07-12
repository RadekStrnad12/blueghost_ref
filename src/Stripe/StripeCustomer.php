<?php
namespace App\Stripe;

use App\Entity\Orders\Order;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * Gets customer from Stripe.
 */
class StripeCustomer
{
    private ContainerBagInterface $containerBag;
    private EntityManagerInterface $entityManager;

    public function __construct(ContainerBagInterface $containerBag, EntityManagerInterface $entityManager)
    {
        $this->containerBag = $containerBag;
        $this->entityManager = $entityManager;
    }

    public function getCustomerId(Order $order, ?User $user = null): string
    {
        if ($user !== null && $user->getStripeCustomerId() !== null)
        {
            return $user->getStripeCustomerId();
        }

        $stripe = new \Stripe\StripeClient($this->containerBag->get('stripe.secret_key'));
        $response = $stripe->customers->create([
            'address' => [
                'city' => $order->getBillingTown(),
                'country' => 'CZ',
                'line1' => $order->getBillingStreet() . " " . $order->getBillingHouseNumber(),
                'postal_code' => $order->getBillingPostCode()
            ],
            'email' => $order->getEmail(),
            'name' => $order->getBillingName() . " " . $order->getBillingSurname()
        ]);

        if ($user !== null)
        {
            $user->setStripeCustomerId($response->id);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $response->id;
    }
}