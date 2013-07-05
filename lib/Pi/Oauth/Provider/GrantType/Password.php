<?php
namespace Pi\Oauth\Provider\GrantType;

use Pi\Oauth\Service;

class Password extends AbstractGrantType
{
    protected $identifier = 'password';

    protected function validateRequest()
    {
        $request = $this->getRequest();
        if (!$request->getRequest('client_id')) {
            $this->setError('invalid_request');
            return false;
        }
        if (!$request->getRequest('password') || !$request->getRequest('username')) {
            $this->setError('invalid_request');
            return false;
        }

        return true;
    }

    protected function authenticate()
    {
        $request = $this->getRequest();
        $username = $request->getRequest('username');
        $password = $request->getRequest('password');
        if (!Service::storage('resource_owner')->validate($username, $password)) {
            $this->setError('invalid_grant');
            return false;
        }

        return true;
    }

    public function createToken()
    {
        // @see http://tools.ietf.org/html/rfc6749#section-4.3.3 Optional for password grant_type
        $createFreshToken = Service::server('grant')->hasGrantType('refresh_token');
        $token = parent::createToken($createFreshToken);

        return $token;
    }
}