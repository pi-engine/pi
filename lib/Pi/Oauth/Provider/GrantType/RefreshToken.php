<?php
namespace Pi\Oauth\Provider\GrantType;

use Pi\Oauth\Service;

/**
 * Create refresh_code if required
 *
 * @see http://tools.ietf.org/html/rfc6749#section-4.1.4 Optional for authorization_code grant_type
 * @see http://tools.ietf.org/html/rfc6749#section-4.2.2 Must not for implicit grant_type
 * @see http://tools.ietf.org/html/rfc6749#section-4.3.3 Optional for password grant_type
 * @see http://tools.ietf.org/html/rfc6749#section-4.4.3 Must not for client_credentials grant_type
 */
class RefreshToken extends AbstractGrantType
{
    protected $identifier = 'refresh_token';

    protected function validateRequest()
    {
        $request = $this->getRequest();
        if (!$request->getRequest('client_id')) {
            $this->setError('invalid_request');
            return false;
        }
        if (!$request->getRequest('refresh_token')) {
            $this->setError('invalid_request');
            return false;
        }

        return true;
    }

    protected function authenticate()
    {
        $request = $this->getRequest();
        $token = $request->getRequest('refresh_token');
        $tokenata = Service::storage('refresh_code')->get($token);
        if (!$tokenata) {
            $this->setError('invalid_grant');
            return false;
        }

        if ($tokenata['client_id'] != $request->getRequest('client_id')) {
            $this->setError('invalid_grant');
            return false;
        }

        return true;
    }
}