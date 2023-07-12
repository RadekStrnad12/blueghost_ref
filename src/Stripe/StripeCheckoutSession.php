<?php
namespace App\Stripe;

use App\Entity\Orders\Order;
use App\Entity\Orders\OrderItem;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Gets URL for payment
 */
class StripeCheckoutSession
{
    private ContainerBagInterface $containerBag;
    private RouterInterface $router;

    public function __construct(ContainerBagInterface $containerBag, RouterInterface $router)
    {
        $this->containerBag = $containerBag;
        $this->router = $router;
    }

    public function createCheckoutSession(Order $order, string $customerId): string
    {
        $items = [];

        /** @var OrderItem $item */
        foreach ($order->getItems() as $item)
        {
            $items[] = [
                'price' => $item->getGame()->getStripePriceId(),
                'quantity' => $item->getAmount(),
            ];
        }

        $items[] = [
            'price' => $order->getDelivery()->getStripePriceId(),
            'quantity' => 1,
        ];

        $stripe = new \Stripe\StripeClient($this->containerBag->get('stripe.secret_key'));
        $response = $stripe->checkout->sessions->create([
            'success_url' => $this->router->generate('app_checkout_payment_successful', [
                "id" => $order->getUuid()->toRfc4122()
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->router->generate('app_order', [
                "id" => $order->getUuid()->toRfc4122()
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'line_items' => $items,
            'mode' => 'payment',
            'customer' => $customerId,
            'currency' => 'czk',
        ]);

        return $response->url;
    }
}