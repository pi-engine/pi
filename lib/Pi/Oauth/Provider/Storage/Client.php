<?php
namespace Pi\Oauth\Provider\Storage;

class Client extends AbstractStorage implements ValidateInterface
{
    public function validate($id, $secret)
    {
        return $this->model->validate($id, $secret);
    }
}