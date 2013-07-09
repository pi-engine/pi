<?php
namespace Oauth\Provider\Storage\Model\Database;

use Pi\Oauth\Provider\Storage\ValidateInterface;

class Client extends AbstractModel implements ValidateInterface
{
    public function validate($username, $password)
    {
        $result = false;
        $row = $this->model->find($username, 'identity');
        if ($row) {
            if ($row->transformCredential($password) == $row->getCredential()) {
                $result = true;
            }
        }
        return $result;
    }
}