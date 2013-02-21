<?php
namespace Pi\Oauth\Provider\GrantType;

use Pi\Oauth\Provider\Service;
use Pi\Oauth\Provider\Http\Request;
use Pi\Oauth\Provider\Result;

abstract class AbstractGrantType
{
    protected $config = array();
    protected $identifier;
    protected $request;
    protected $result;

    public function __construct(array $config = array())
    {
        $this->setConfig($config);
    }

    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        if (!$this->request) {
            $this->request = new Request;
        }

        return $this->request;
    }

    public function setError($error, $errorDescription = null, $errorUri = null, $statusCode = 400)
    {
        $this->result = Service::error('grant_error', $error, $errorDescription, $errorUri, $statusCode);
        return $this;
    }

    /**
     * Get result
     *
     * @return Result\Result
     */
    public function getResult()
    {
        return $this->result;
    }

    public function process(Request $request)
    {
        $this->setRequest($request);
        if (!$this->validateRequest()) {
            return false;
        }
        if (!$this->authenticate()) {
            return false;
        }
        $tokenData = $this->createToken();
        return $tokenData;
    }

    public function createToken($createRreshToken = false)
    {
        $request = $this->getRequest();
        $params = array(
            'client_id' => $request->getRequest('client_id'),
            'scope'     => $request->getRequest('scope'),
        );
        $tokenData = Service::storage('access_token')->add($params);

        if ($createRreshToken) {
            $refreshToken = Service::storage('refresh_token')->add(array('client_id' => $request->getRequest('client_id')));
            $tokenData['refresh_token'] = $refreshToken;
        }
        return $tokenData;
    }

    abstract protected function validateRequest();
    abstract protected function authenticate();
}