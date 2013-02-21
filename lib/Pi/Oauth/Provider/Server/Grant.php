<?php
namespace Pi\Oauth\Provider\Server;

use Pi\Oauth\Provider\Service;
use Pi\Oauth\Provider\Http\Request;
use Pi\Oauth\Provider\GrantType\AbstractGrantType;

/**
 * Authorization server methods
 * - grant:
 */
class Grant extends AbstractServer
{
    protected $grantTypes = array(
        'authorization_code'    => 'AuthorizationCode',
        'password'              => 'Password',
        'client_credentials'    => 'ClientCredentials',
        'refresh_token'         => 'RefreshToken',
        'urn:ietf:params:oauth:grant-type:jwt-bearer'   => 'JwtBearer',
    );
    protected $resultType = 'grant_response';
    protected $errorType = 'grant_error';

    public function setConfig(array $config)
    {
        if (isset($config['grant_types'])) {
            $this->setGrantTypes($config['grant_types']);
            unset($config['grant_types']);
        }

        parent::setConfig($config);
        return $this;
    }

    public function setGrantTypes(array $types)
    {
        $this->grantTypes = $types;
        return $this;
    }

    public function hasGrantType($type)
    {
        return isset($this->grantTypes[$type]) ? true : false;
    }

    protected function grantType($type)
    {
        if (!isset($this->grantTypes[$type])) {
            return false;
        }

        if (!$this->grantTypes[$type] instanceof AbstractGrantType) {
            $this->grantTypes[$type] = Service::grantType($type);
        }

        return $this->grantTypes[$type];
    }

    /**
     * add a grant_type
     *
     * @param AbstractGrantType|string $grantType The grant type to add for the specified identifier
     * @param string|null $identifier A string passed in as "grant_type" in the response that will call this grantType
     **/
    public function addGrantType($grantType, $identifier = null)
    {
        if (!$identifier && $grantType instanceof AbstractGrantType) {
            $identifier = $grantType->getIdentifier();
        }
        if ($identifier) {
            $this->grantTypes[$identifier] = $grantType;
        }

        return $this;
    }

    protected function validateRequest()
    {
        $request = $this->getRequest();

        /**
         * @see http://tools.ietf.org/html/rfc6749#section-3.2
         */
        if (!$request->isPost()) {
            $this->setError('invalid_request', 'The client MUST use the HTTP "POST" method when making access token requests.');
            return false;
        }

        // Determine grant type from request
        $grantType = $request->getRequest('grant_type');
        if (!$grantType) {
            $this->setError('invalid_request', 'The grant type was not specified in the request');
            return false;
        }

        return true;
    }

    public function process(Request $request = null)
    {
        $this->setRequest($request);
        if (!$this->validateRequest()) {
            return false;
        }
        $grantType = $this->grantType($request->getRequest('grant_type'));
        $token = $grantType->process($request);
        if ($token) {
            $this->setResult($token);
        } else {
            $this->result = $grantType->getResult();
            return false;
        }
        return true;
    }
}