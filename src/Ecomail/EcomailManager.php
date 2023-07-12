<?php

namespace App\Ecomail;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Wrapper for sending e-mails
 */
class EcomailManager
{
    public const ECOMAIL_TRANSACTIONAL_MAIL_URL = 'https://api2.ecomailapp.cz/transactional/send-template';

    private HttpClientInterface $httpClient;
    private ContainerBagInterface $containerBag;

    public function __construct(HttpClientInterface $httpClient, ContainerBagInterface $containerBag)
    {
        $this->httpClient = $httpClient;
        $this->containerBag = $containerBag;
    }

    public function sendRequest(string $url = self::ECOMAIL_TRANSACTIONAL_MAIL_URL, string $method = 'POST', string $body = ''): bool
    {
        try
        {
            $response = $this->httpClient->request($method, $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "key" => $this->containerBag->get("ecomail.key")
                ],
                'body' => $body
            ]);

            return true;
        }
        catch (\Exception $exception)
        {
            return false;
        }
    }
}