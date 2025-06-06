<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MapService {

    public function __construct(private HttpClientInterface $httpClient,
                                private ParameterBagInterface $params) { }

    public function calcularCoordenadas(string $direccion): ?array
    {
        $url_nominatim = $this->params->get('url_nominatim');
        $url = $url_nominatim . '/search';
        
        $query = sprintf(
            '%s, %s, %s, %s',
            $direccion ?? '',
            'Partido de La Plata',
            'Buenos Aires',
            'Argentina'
        );

        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'q' => $query,
                'format' => 'json',
                'direccion' => 1,
                'limit' => 1,
            ],
        ]);
        
        $data = $response->toArray();
       
        if (!empty($data)) {
            return [
                'lat' => $data[0]['lat'],
                'lon' => $data[0]['lon'],
            ];
        }
        return null;
    }


     public function calcularCoordenadasGeneral(string $direccion, string $ciudad): ?array
    {
        $url_nominatim = $this->params->get('url_nominatim');
        $url = $url_nominatim . '/search';
        
        $query = sprintf(
            '%s, %s, %s, %s',
            $direccion ?? '',
            $ciudad ?? '',
            'Buenos Aires',
            'Argentina'
        );

        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'q' => $query,
                'format' => 'json',
                'direccion' => 1,
                'limit' => 1,
            ],
        ]);
        
        $data = $response->toArray();
       
        if (!empty($data)) {
            return [
                'lat' => $data[0]['lat'],
                'lon' => $data[0]['lon'],
            ];
        }
        return null;
    }

}