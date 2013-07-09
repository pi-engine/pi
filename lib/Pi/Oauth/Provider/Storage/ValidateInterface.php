<?php
namespace Pi\Oauth\Provider\Storage;

interface ValidateInterface extends ModelInterface
{
    public function validate($id, $secret);
}