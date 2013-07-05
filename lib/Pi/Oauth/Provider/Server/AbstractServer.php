<?php
namespace Pi\Oauth\Provider\Server;

use Pi\Oauth\Provider\Service;
use Pi\Oauth\Provider\Http\Request;

abstract class AbstractServer
{
    protected $request;
    protected $config = array();
    protected $result;
    protected $resultType = 'response';
    protected $errorType = 'error';

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

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        if (!$this->request) {
            $this->request = Service::request();
        }

        return $this->request;
    }

    abstract public function process(Request $request);

    /**
     * Create result
     *
     * @param array $params
     * @return AbstractServer
     */
    public function setResult(array $params = array())
    {
        $this->result = Service::result($this->resultType, $params);
        return $this;
    }

    /**
     * Create error result
     *
     * @param string $errorCode
     * @param string $errorDescription
     * @param string $errorUri
     * @param int $statusCode
     * @return AbstractServer
     */
    public function setError($error, $errorDescription = null, $errorUri = null, $statusCode = 400)
    {
        $this->result = Service::error($this->errorType, $error, $errorDescription, $errorUri, $statusCode);
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