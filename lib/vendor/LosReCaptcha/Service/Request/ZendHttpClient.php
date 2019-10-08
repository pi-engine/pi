<?php
namespace LosReCaptcha\Service\Request;

use LosReCaptcha\Service\ReCaptcha;
use Zend\Http\Client;

final class ZendHttpClient implements RequestInterface
{
    /**
     * HTTP client
     *
     * @var Client
     */
    protected $client;

    /**
     * Constructor
     *
     * @param Client $client HTTP client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ? $client : new Client();
    }

    /**
     * Submit ReCaptcha API request, return response body.
     *
     * @param Parameters $params ReCaptcha parameters
     *
     * @return string
     */
    public function send(Parameters $params)
    {
        $this->client->setUri(ReCaptcha::VERIFY_SERVER);
        $this->client->setRawBody($params->toQueryString());
        $this->client->setEncType('application/x-www-form-urlencoded');
        $result = $this->client->setMethod('POST')->send();
        return $result ? $result->getBody() : null;
    }
}
