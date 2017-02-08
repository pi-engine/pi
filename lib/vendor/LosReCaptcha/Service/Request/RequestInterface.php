<?php
namespace LosReCaptcha\Service\Request;

interface RequestInterface
{
    public function send(Parameters $params);
}
