<?php
namespace Pi\Oauth\Provider\Storage;

class AccessToken extends AbstractStorage implements CodeInterface
{
    public function add($params)
    {
        if (!isset($params['token'])) {
            $params['token'] = $this->generateCode($this->config['length']);
        }

        parent::add($params);
        $params['token_type'] = $this->config['token_type'];
        return $params;
    }
}