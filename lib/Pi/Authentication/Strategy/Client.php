<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Authentication\Strategy;

use Pi;

/**
 * Authentication strategy
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Client extends Local
{
    /**
     * {@inheritDoc}
     */
    protected $name = 'client';

    /**
     * {@inheritDoc}
     */
    public function getUrl($type, $params = null)
    {
        switch ($type) {
            case 'login':
            case 'logout':
                if ($params && is_string($params)) {
                    $params = array(
                        'redirect' => $params,
                    );
                }
                $url = Pi::api('user', 'system')->getUrl($type, $params);
                //$url = Pi::service('user')->getUrl($type, $params);
                break;
            default:
                $url = '';
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function login(array $params = array())
    {
        $url = $this->getUrl('login', $params);
        Pi::service('url')->redirect($url);
    }

    /**
     * {@inheritDoc}
     */
    public function logout(array $params = array())
    {
        $url = $this->getUrl('logout', $params);
        Pi::service('url')->redirect($url, true);
    }
}
