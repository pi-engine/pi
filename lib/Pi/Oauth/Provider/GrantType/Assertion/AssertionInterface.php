<?php
namespace Oauth\GrantType\Assertion;

use Oauth\GrantType\GrandTypeInterface;

interface AssertionInterface
{
    public function getClientDataFromRequest(RequestInterface $request);
    public function validateClientData(array $clientData);
}
