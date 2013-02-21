<?php
namespace Pi\Oauth\Provider\Result;

use Pi\Oauth\Provider\Http\Response as HttpResponse;

class Response extends HttpResponse
{
    protected $errorType;

    public function __construct(array $params = array())
    {
        $this->setParams($params);
    }

    public function errorType($errorType = null)
    {
        if (null === $errorType) {
            return $this->errorType;
        }
        $this->errorType = $errorType;
        return $this;
    }
}