<?php
namespace Pi\Oauth\Provider\Storage\Model;

use Pi;
use Pi\Oauth\Provider\Service;

class Loader implements LoaderInterface
{
    public static function load($identifier)
    {
        $class = __NAMESPACE__ . '\\' . Service::canonizeName($identifier);
        if ('resource_owner' == $identifier) {
            $model = Pi::model('user');
        } else {
            $model = Pi::model($identifier);
        }
        return new $class($model);
    }
}