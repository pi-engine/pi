<?php
namespace LosReCaptcha\Service\Request;

use LosReCaptcha\Service\ReCaptcha;

final class Curl implements RequestInterface
{
    private $curl;

    public function __construct($curl = null)
    {
        if (! is_null($curl)) {
            $this->curl = $curl;
        } else {
            $this->curl = curl_init();
            curl_setopt_array($this->curl, [
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
                CURLINFO_HEADER_OUT => false,
                CURLOPT_HEADER => false,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
        }
    }

    public function send(Parameters $params)
    {
        curl_setopt_array($this->curl, [
            CURLOPT_URL => ReCaptcha::VERIFY_SERVER,
            CURLOPT_POSTFIELDS => $params->toQueryString(),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $response = curl_exec($this->curl);
        curl_close($this->curl);

        return $response;
    }
}
