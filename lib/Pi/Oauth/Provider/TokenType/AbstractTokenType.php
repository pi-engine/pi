<?php
namespace Pi\Oauth\Provider\TokenType;

use Pi\Oauth\Provider\Service;
use Pi\Oauth\Provider\Http\Request;
//use Pi\Oauth\Provider\Result;

/**
 * @see http://tools.ietf.org/html/rfc6749#section-8.1
 */
abstract class AbstractTokenType
{
    protected $config = array();
    protected $error;
    protected $identifier;

    /**
     * @param array $config
     */
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

    abstract public function getAccessToken(Request $request);

    /**
     * Create error result
     *
     * @param array $params
     * @return AbstractTokenType
     */
    public function setResult(array $params = array())
    {
        $this->result = Service::result('response', $params);
        return $this;
    }

    /**
     * Create error result
     *
     * @param string $errorCode
     * @param string $errorDescription
     * @param string $errorUri
     * @param int $statusCode
     * @return AbstractTokenType
     */
    public function setError($error, $errorDescription = null, $errorUri = null, $statusCode = 400)
    {
        $this->result = Service::error('error', $error, $errorDescription, $errorUri, $statusCode);
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
}