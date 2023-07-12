<?php

namespace App\Ecomail\Order;

use App\Ecomail\EcomailManager;
use App\Entity\Orders\Order;
use App\Tools\PrintPrice;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Sending e-mail after creating a new order
 */
class EcomailOrder
{
    private EcomailManager $ecomailManager;
    private ContainerBagInterface $containerBag;
    private RouterInterface $router;

    public function __construct(EcomailManager $ecomailManager, ContainerBagInterface $containerBag, RouterInterface $router)
    {
        $this->containerBag = $containerBag;
        $this->ecomailManager = $ecomailManager;
        $this->router = $router;
    }

    public function sendEmail(Order $order): bool
    {
        try
        {
            $json = json_encode((object) ["message" => (object) [
                "template_id" => 7,
                "subject" => "Rekapitulace objednávky | Quest Place",
                "from_name" => "Quest Place",
                "from_email" => "info@questplace.cz",
                "to" => [
                    (object) [
                        "email" => $order->getEmail()
                    ]
                ],
                "global_merge_vars" => [
                    (object) [
                        "name" => "ORDER_DATE",
                        "content" => $order->getCreatedAt()->format("d.m.Y H:i")
                    ],
                    (object) [
                        "name" => "ORDER_NUMBER",
                        "content" => $order->getId()
                    ],
                    (object) [
                        "name" => "PAYMENT_METHOD",
                        "content" => strtolower($order->getPaymentMethod()->getName())
                    ],
                    (object) [
                        "name" => "BILLING_NAME",
                        "content" => $order->getBillingName() . " " . $order->getBillingSurname()
                    ],
                    (object) [
                        "name" => "DELIVERY_NAME",
                        "content" => $order->getDeliveryName() . " " . $order->getDeliverySurname()
                    ],
                    (object) [
                        "name" => "BILLING_ADDRESS",
                        "content" => $order->getBillingStreet() . " " . $order->getBillingHouseNumber()
                    ],
                    (object) [
                        "name" => "DELIVERY_ADDRESS",
                        "content" => $order->getDeliveryStreet() . " " . $order->getDeliveryHouseNumber()
                    ],
                    (object) [
                        "name" => "BILLING_TOWN",
                        "content" => $order->getBillingPostCode() . " " . $order->getBillingTown()
                    ],
                    (object) [
                        "name" => "DELIVERY_TOWN",
                        "content" => $order->getDeliveryPostCode() . " " . $order->getDeliveryTown()
                    ],
                    (object) [
                        "name" => "DELIVERY_FULL",
                        "content" => $order->getDelivery()->getId() == 2
                    ],
                    (object) [
                        "name" => "PAYMENT_PREVOD",
                        "content" => $order->getPaymentMethod()->getId() == 1
                    ],
                    (object) [
                        "name" => "DELIVERY_ZASILKOVNA",
                        "content" => $order->getDelivery()->getName()
                    ],
                    (object) [
                        "name" => "BANK_ACCOUNT",
                        "content" => $this->containerBag->get("bank_account.full")
                    ],
                    (object) [
                        "name" => "VARIABLE_NUMBER",
                        "content" => $order->getId()
                    ],
                    (object) [
                        "name" => "ORDER_TOTAL_PRICE",
                        "content" => PrintPrice::printPrice($order->getPrice())
                    ],
                    (object) [
                        "name" => "ORDER_PAYMENT_METHOD",
                        "content" => $order->getPaymentMethod()->getName()
                    ],
                    (object) [
                        "name" => "DETAIL_URL",
                        "content" => $this->router->generate("app_order", [
                            "id" => $order->getUuid()->toRfc4122()
                        ], UrlGeneratorInterface::ABSOLUTE_URL)
                    ],
                ]
            ]]);

            $this->ecomailManager->sendRequest(body: $json);

            return true;
        }
        catch (\Exception $exception)
        {
            return false;
        }
    }
}