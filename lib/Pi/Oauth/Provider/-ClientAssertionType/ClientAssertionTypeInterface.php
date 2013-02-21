<?php
namespace Oauth\ClientAssertionType;

interface ClientAssertionTypeInterface
{
    public function getClientDataFromRequest(RequestInterface $request);
    public function validateClientData(array $clientData);
}
