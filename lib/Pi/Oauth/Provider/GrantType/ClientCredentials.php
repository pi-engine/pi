<?php
namespace Pi\Oauth\Provider\GrantType;

use Pi\Oauth\Service;

class ClientCredentials extends AbstractGrantType
{
    protected $identifier = 'client_credentials';

    protected function validateRequest()
    {
        $request = $this->getRequest();
        if (!$request->getRequest('client_id') || !$request->getRequest('client_secret')) {
            $this->setError('invalid_request');
            return false;
        }

        return true;
    }

    protected function authenticate()
    {
        $request = $this->getRequest();
        $client_id = $request->getRequest('client_id');
        $client_secret = $request->getRequest('client_secret');
        if (!Service::storage('client')->validate($client_id, $client_secret)) {
            $this->setError('invalid_client');
            return false;
        }

        return true;
    }
}