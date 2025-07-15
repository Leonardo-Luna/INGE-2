<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class QRService
{
    private HttpClientInterface $httpClient;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(HttpClientInterface $httpClient, UrlGeneratorInterface $urlGenerator)
    {
        $this->httpClient = $httpClient;
        $this->urlGenerator = $urlGenerator;
    }

    public function generarOrdenQr($total, $idReserva, $urlWebhook): ?string
    {
        $url = 'https://api.mercadopago.com/instore/orders/qr/seller/collectors/2476843319/pos/SUC001POS001/qrs';

        $token = $_ENV['MERCADOPAGO_PRESENCIAL_ACCESS_TOKEN'];

        $data = [
            "external_reference" => (string)$idReserva,
            "description" => "Pago de reserva",
            "title" => "Reserva de Maquina",
            "notification_url" => $urlWebhook,
            "total_amount" => (float)$total,
            "items" => [
                [
                    "title" => "Reserva de Maquina",
                    "quantity" => 1,
                    "unit_price" => (float)$total,
                    "total_amount" => (float)$total,
                    "unit_measure" => "unit",
                ]
            ],
        ];

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'json' => $data
        ]);

        $array = $response->toArray(false);
        return $array['qr_data'] ?? null;
    }
}