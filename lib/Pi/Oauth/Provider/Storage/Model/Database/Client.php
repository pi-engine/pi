<?php
namespace Oauth\Provider\Storage\Model\Database;

use Pi\Oauth\Provider\Storage\ValidateInterface;

class Client extends AbstractModel implements ValidateInterface
{
    public function validate($id, $secret)
    {
        $rowset = $this->model->select(array(
            'client_id'     => $id,
            'client_secret' => $secret,
        ));
        return $rowset->count() == 1 ? true : false;
    }
}