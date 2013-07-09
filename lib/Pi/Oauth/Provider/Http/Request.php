<?php
namespace Pi\Oauth\Provider\Http;

use Zend\Http\PhpEnvironment\Request as HttpRequest;

class Request extends HttpRequest
{
    public function getRequest($name)
    {
        $result = $this->getPost($name);
        if (null === $result) {
            $result = $this->getQuery($name);
        }

        return $result;
    }
}