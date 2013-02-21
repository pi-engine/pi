<?php
namespace Pi\Oauth\Provider\Result;

use Oauth\Provider\Http\Response as HttpResponse;

class Redirect extends HttpResponse
{
    public function __construct($uri)
    {
        $this->addHeaderLine('Location', $uri);
        $this->setStatusCode(302);
    }
}