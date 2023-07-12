<?php

namespace App\Fakturoid;

use App\Entity\Orders\Order;
use App\Entity\Orders\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Create and update invoices in Fakturoid
 */
class FakturoidInvoice
{
    private ContainerBagInterface $containerBag;
    private HttpClientInterface $httpClient;
    private EntityManagerInterface $entityManager;

    public function __construct(ContainerBagInterface $containerBag, HttpClientInterface $httpClient, EntityManagerInterface $entityManager)
    {
        $this->containerBag = $containerBag;
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
    }

    public function createInvoice(Order $order, int $fakturoidId): void
    {
        // ------------------------------------------------------------------------
        // prepare items for invoice

        $items = [];

        /** @var OrderItem $item */
        foreach ($order->getItems() as $item)
        {
            $items[] = (object) [
                "name" => $item->getGame()->getNameFaktura(),
                "quantity" => $item->getAmount(),
                "unit_name" => "ks",
                "unit_price" => ($item->getGame()->getPrice() / 100),
                "vat_rate" => 0
            ];
        }

        $items[] = (object) [
            "name" => "Doručení: " . $order->getDelivery()->getName() . ($order->getZasilkovnaDeliveryPlaceText() != null ? ' ('.$order->getZasilkovnaDeliveryPlaceText().')' : ''),
            "quantity" => "1",
            "unit_name" => "ks",
            "unit_price" => ($order->getDelivery()->getPrice() / 100),
            "vat_rate" => 0
        ];

        if ($order->getPaymentMethod()->getPrice() > 0)
        {
            $items[] = (object) [
                "name" => "Způsob úhrady: " . $order->getPaymentMethod()->getName(),
                "quantity" => "1",
                "unit_name" => "ks",
                "unit_price" => ($order->getPaymentMethod()->getPrice() / 100),
                "vat_rate" => 0
            ];
        }

        // ------------------------------------------------------------------------
        // invoice data

        $json = json_encode((object) [
            "custom_id" => $order->getId(),
            "subject_id" => $fakturoidId,
            "due" => 7,
            "variable_symbol" => $order->getId(),
            "payment_method" => $order->getPaymentMethod()->getFakturoidCode(),
            "bank_account" => $this->containerBag->get("bank_account.full"),
            "tags" => ["Quest Place"],
            "lines" => $items
        ]);

        $response = $this->httpClient->request('POST', 'https://app.fakturoid.cz/api/v2/accounts/'.$this->containerBag->get("fakturoid.slug").'/invoices.json', [
            'headers' => [
                'Content-Type' => 'application/json',
                "User-Agent" => $this->containerBag->get("fakturoid.useragent")
            ],
            'body' => $json,
            'auth_basic' => [$this->containerBag->get("fakturoid.email"), $this->containerBag->get("fakturoid.key")],
        ]);

        $data = json_decode($response->getContent());
        $fakturoidId = $data->id;
        $fakutoidInvoiceUrl = $data->public_html_url;
        $fakturoidInvoiceNumber = $data->number;

        $order->setFakturoidId($fakturoidId);
        $order->setFakturoidInvoiceNumber($fakturoidInvoiceNumber);
        $order->setFakturoidInvoiceUrl($fakutoidInvoiceUrl);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

    public function setInvoicePaid(string $invoiceId): void
    {
        $response = $this->httpClient->request('POST', 'https://app.fakturoid.cz/api/v2/accounts/'.$this->containerBag->get("fakturoid.slug").'/invoices/' . $invoiceId . '/fire.json?event=pay', [
            'headers' => [
                'Content-Type' => 'application/json',
                "User-Agent" => $this->containerBag->get("fakturoid.useragent")
            ],
            'auth_basic' => [$this->containerBag->get("fakturoid.email"), $this->containerBag->get("fakturoid.key")],
        ]);
    }
}