<?php
namespace Pi\Oauth\Provider\GrantType;

use Pi\Oauth\Service;

class AuthorizationCode extends AbstractGrantType
{
    protected $identifier = 'authorization_code';

    protected function validateRequest()
    {
        $request = $this->getRequest();
        if (!$request->getRequest('client_id')) {
            $this->setError('invalid_request');
            return false;
        }
        if (!$request->getRequest('code')) {
            $this->setError('invalid_request');
            return false;
        }

        return true;
    }

    protected function authenticate()
    {
        $request = $this->getRequest();
        $code = $request->getRequest('code');
        $codeData = Service::storage('authorization_code')->get($code);
        if (!$codeData) {
            $this->setError('invalid_grant');
            return false;
        }

        if ($codeData['client_id'] != $request->getRequest('client_id')) {
            $this->setError('invalid_grant');
            return false;
        }

        /*
         * 4.1.3 - ensure that the "redirect_uri" parameter is present if the "redirect_uri" parameter was included in the initial authorization request
         * @uri - http://tools.ietf.org/html/rfc6749#section-4.1.3
         */
        if (!empty($codeData['redirect_uri'])) {
            if (!$request->request('redirect_uri') || urldecode($request->request('redirect_uri')) != $tokenData['redirect_uri']) {
                $this->setError('invalid_grant');
                return false;
            }
        } elseif (!$request->request('redirect_uri')) {
            $this->setError('invalid_grant');
            return false;
        }

        return true;
    }

    public function createToken()
    {
        $request = $this->getRequest();
        Service::storage('authorization_code')->delete($request->getRequest('code'));

        // @see http://tools.ietf.org/html/rfc6749#section-4.1.4 Optional for authorization_code grant_type
        $createFreshToken = Service::server('grant')->hasGrantType('refresh_token');
        $token = parent::createToken($createFreshToken);

        return $token;
    }
}