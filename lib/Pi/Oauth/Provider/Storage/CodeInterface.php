<?php
namespace Pi\Oauth\Provider\Storage;

interface CodeInterface extends ModelInterface
{
    public function generateCode($length = null);
    public function expire($expires = null);
}