<?php
namespace Pi\Oauth\Provider\Storage;

class AuthorizationCode extends AbstractStorage implements CodeInterface
{
    public function add($params)
    {
        if (!isset($params['code'])) {
            $params['code'] = $this->generateCode($this->config['length']);
        }

        parent::add($params);
        return $params['code'];
    }
}