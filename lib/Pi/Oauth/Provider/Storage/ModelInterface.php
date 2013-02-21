<?php
namespace Pi\Oauth\Provider\Storage;

interface ModelInterface
{
    public function add($params);
    public function get($id);
    public function update($id, $params);
    public function delete($id);
}