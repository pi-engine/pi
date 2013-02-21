<?php
namespace Pi\Oauth\Provider\Server;

use Pi\Oauth\Provider\Service;
use Pi\Oauth\Provider\Http\Request;
use Pi\Oauth\Provider\TokenType;

/**
 * Resource server methods:
 * - process: validate access token
 */
class Resource extends AbstractServer
{
    protected $tokenType = 'bearer';
    protected $config = array(
        'www_realm' => 'Service',
    );
    protected $errorType = 'resource_error';

    public function setConfig(array $config)
    {
        if (isset($config['token_type'])) {
            if (is_scalar($config['token_type'])) {
                $this->setTokenType($config['token_type']);
            } else {
                $this->setTokenType($config['token_type']['name'], $config['token_type']['config']);
            }
            unset($config['token_type']);
        }

        parent::setConfig($config);
        return $this;
    }

    public function getTokenType()
    {
        if (!$this->tokenType instanceof TokenType\AbstractTokenType) {
            $this->setTokenType();
        }
        return $this->tokenType;
    }

    public function setTokenType($tokenType = null)
    {
        if ($tokenType instanceof TokenType\AbstractTokenType) {
            $this->tokenType = $tokenType;
        } else {
            $this->tokenType = Service::tokenType($tokenType);
        }
        return $this;
    }

    protected function validateRequest()
    {
        $request = $this->getRequest();
        $tokenParam = $this->getTokenType()->getAccessToken($request);
        $this->result = $this->getTokenType()->getResult();
        return $tokenParam;
    }

    public function process(Request $request = null)
    {
        $this->setRequest($request);
        $scope = $request->getRequest('scope');
        $tokenParam  = $this->validateRequest();
        if ($this->result && $this->result->errorType()) {
            return false;
        }

        if (!$tokenParam) { // Access token was not provided
            $this->setError('invalid_request');
            return false;
        }

        // Get the stored token data
        $token = Service::storage('access_token')->get($tokenParam);
        if (!$token) {
            $this->setError('invalid_token', 'The access token provided is invalid');
            return false;
        }

        // Check scope, if provided
        // If token doesn't have a scope, it's null/empty, or it's insufficient, then throw an error
        if (!empty($token['scope'])) {
            $grantedScope = Service::scope($token['scope']);
            $requiredScope = Service::scope($scope);
            if (!$grantedScope->hasScope($requiredScope)) {
                $this->setError('insufficient_scope');
                return false;
            }
        }

        return true;
    }
}