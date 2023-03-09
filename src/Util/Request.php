<?php

namespace Supabase\Util;

use Psr\Http\Message\ResponseInterface;

class Request
{
    public static function request($method, $url, $headers, $body = null): ResponseInterface
    {
        try {
            $request = new \GuzzleHttp\Psr7\Request($method, $url, $headers, $body);
            $client = new \GuzzleHttp\Client();
            $promise = $client->sendAsync($request)->then(function ($response) {
                return $response;
            });

            $response = $promise->wait();

            return $response;
        } catch (\Exception $e) {
            print_r($e->getMessage());

            throw self::handleError($e);
        }
    }

    public static function handleError($error)
    {
        if (method_exists($error, 'getResponse')) {
            $response = $error->getResponse();
            $data = json_decode($response->getBody(), true);
            print_r($data);
            $error = new PostgrestApiError($data['code'], $data['details'], $data['hint'], $data['message'], $response);
        } else {
            $error = new PostgrestUnknownError($error->getMessage(), $error->getCode());
        }

        return $error;
    }
}
