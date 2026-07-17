<?php

namespace Corbital\Rightful\Helpers;

use Exception;
use Symfony\Component\HttpClient\HttpClient;
use WpOrg\Requests\Exception as Requests_Exception;
use WpOrg\Requests\Requests;

class RequestHelper
{
    // Define constants at the class level
    private const KB = 1024;
    private const MB = 1048576;
    private const GB = 1073741824;

    /**
     * Make an HTTP request using the rmccue/requests library.
     *
     * @param string     $method  HTTP method ('GET', 'POST', 'PUT', 'DELETE', etc.).
     * @param string     $url     full API endpoint or URL
     * @param array|null $data    optional request data
     * @param array      $headers custom headers to attach to the request
     */
    public static function makeRequest(string $method, string $url, ?array $data = null, array $headers = [])
    {
        $response = HttpClient::create()->request('POST', $url, [
            'headers' => $headers,
            'json' => $data,
        ]);

        return [
            'status_code' => $response->getStatusCode(),
            'body' => $response->getContent(false),
        ];
    }

    /**
     * Execute an HTTP request and verify the response status code.
     *
     * @param string     $method  HTTP method
     * @param string     $url     full API endpoint or URL
     * @param array|null $data    optional request data
     * @param array      $headers custom headers to attach to the request
     */
    public static function executeAndVerifyResponse(string $method, string $url, ?array $data = null, array $headers = [])
    {
        $response = self::makeRequest($method, $url, $data, $headers);

        return $response;
    }
}
