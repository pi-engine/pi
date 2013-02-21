<?php
namespace Pi\Oauth\Provider\TokenType;

use Pi\Oauth\Provider\Http\Request;

class Mac extends AbstractTokenType
{
    protected $identifier = 'mac';

    public function getAccessToken(Request $request)
    {
        throw new \LogicException('Not supported');
    }
}