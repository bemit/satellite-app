<?php

namespace App\Commands;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Satellite\KernelConsole\Annotations\Command;

/**
 * HTTP Client demo
 *
 * @Command(
 *     name="countries:europe",
 *     handler="handle",
 * )
 */
class DemoHttpClient {

    protected RequestFactoryInterface $request_factory;
    protected ClientInterface $http_client;

    public function __construct(ClientInterface $http_client, RequestFactoryInterface $request_factory) {
        $this->http_client = $http_client;
        $this->request_factory = $request_factory;
    }

    public function handle(\GetOpt\Command $command) {
        error_log('Listing countries...');
        $request = $this->request_factory
            ->createRequest(
                'GET',
                'https://restcountries.com/v3.1/region/europse',
            )
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json');
        try {
            $result = $this->http_client->sendRequest($request);
            if($result->getStatusCode() < 200 || $result->getStatusCode() >= 300) {
                error_log('API Response Error: ' . $result->getStatusCode() . ', ' . $result->getBody());
                return;
            }
            $data = json_decode($result->getBody(), false, 512, JSON_THROW_ON_ERROR);
            error_log('API Result: current countries in europe: ' . count($data));
            error_log('first country in list: ' . array_shift($data)->name->official);
            error_log('last country in list: ' . array_pop($data)->name->official);
        } catch(NetworkExceptionInterface $e) {
            error_log('API Request Connection Error: ' . $e->getMessage());
        } catch(RequestExceptionInterface $e) {
            error_log('API Request Error: ' . $e->getMessage());
        }
    }
}
