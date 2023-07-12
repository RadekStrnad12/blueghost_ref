<?php

namespace App\Fakturoid;

use App\Entity\Orders\Order;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Gets customer from Fakturoid.
 */
class FakturoidSubject
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

    public function getSubject(Order $order, ?User $user = null): int
    {
        if ($user === null || $user->getFakturoidId() === null)
        {
            $json = json_encode((object) [
                "custom_id" => $user?->getId(),
                "name" => $order->getBillingName() . " " . $order->getBillingSurname(),
                "street" => $order->getBillingStreet() . " " . $order->getBillingHouseNumber(),
                "city" => $order->getBillingTown(),
                "zip" => $order->getBillingPostCode(),
                "email" => $order->getEmail(),
            ]);

            $response = $this->httpClient->request('POST', 'https://app.fakturoid.cz/api/v2/accounts/'.$this->containerBag->get("fakturoid.slug").'/subjects.json', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "User-Agent" => $this->containerBag->get("fakturoid.useragent")
                ],
                'body' => $json,
                'auth_basic' => [$this->containerBag->get("fakturoid.email"), $this->containerBag->get("fakturoid.key")],
            ]);

            $data = json_decode($response->getContent());
            $fakturoidId = $data->id;

            if ($user !== null)
            {
                $user->setFakturoidId($fakturoidId);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        }
        else
        {
            $fakturoidId = $user->getFakturoidId();
        }

        return $fakturoidId;
    }
}