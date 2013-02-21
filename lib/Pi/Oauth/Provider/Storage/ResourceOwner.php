<?php
namespace Pi\Oauth\Provider\Storage;

class ResourceOwner extends AbstractStorage implements ValidateInterface
{
    public function validate($username, $password)
    {
        return $this->model->validate($username, $password);
    }
}