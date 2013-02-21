<?php
namespace Pi\Oauth\Provider\ResponseType;

abstract class AbstractResponseType
{
    protected $config = array();
    //protected $storage;

    /**
     * @param array $config
     * specify a different token lifetime, token header name, etc
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

    abstract public function process(array $params);
}