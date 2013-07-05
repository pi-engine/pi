<?php
namespace Pi\Oauth\Provider\Result;

class GrantResponse extends Response
{

    public function __construct(array $params = array())
    {
        parent::__construct($params);

        // @see http://tools.ietf.org/html/rfc6749#section-5.1
        // server MUST disable caching in headers when tokens are involved
        $this->addHeaderLine('Cache-Control', 'no-store');
        $this->addHeaderLine('Pragma', 'no-cache');
    }
}