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
use Pi\Authentication\Storage\StorageInterface;
use SimpleSAML_Auth_Simple;
use SimpleSAML_Configuration;

/**
 * Authentication strategy for simplesamlphp
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Saml extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     */
    protected $fileIdentifier = 'saml';

    /**
     * {@inheritDoc}
     */
    protected $name = 'saml';

    /**
     * {@inheritDoc}
     */
    protected $identityField = 'identity';

    /** @var  SimpleSAML_Auth_Simple SimpleSAMLphp handler */
    protected $authSource;

    /**
     * Get SSP authentication source
     *
     * @return SimpleSAML_Auth_Simple
     */
    protected function getAuthSource()
    {
        if (!$this->authSource) {
            $sourceId = $this->getOption('source_id');
            require_once Pi::path('vendor') . '/simplesamlphp/lib/_autoload.php';
            $configPath = Pi::path('config/saml');
            SimpleSAML_Configuration::setConfigDir($configPath, 'simplesaml');
            $this->authSource = new SimpleSAML_Auth_Simple($sourceId);
        }

        return $this->authSource;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($type, $params = null)
    {
        if (is_string($params)) {
            $params = array(
                'redirect'  => $params,
            );
        } else {
            $params = (array) $params;
        }

        if (isset($params['redirect'])) {
            $return = $params['redirect'];
        } else {
            $return = Pi::service('url')->getRequestUri();
        }
        $return = Pi::url($return, true);
        if ('login' == $type) {
            //$url = $this->getAuthSource()->getLoginURL($return);
            $url = Pi::service('url')->assemble('default', array(
                'module'        => 'saml',
                'controller'    => 'index',
                'action'        => 'login',
            ));
        } elseif ('logout' == $type) {
            //$url = $this->getAuthSource()->getLogoutURL($return);
            $url = Pi::service('url')->assemble('default', array(
                'module'        => 'saml',
                'controller'    => 'index',
                'action'        => 'logout',
            ));
        } else {
            $url = '';
        }
        if ($url && $return) {
            $url .= '?redirect=' . rawurlencode($return);
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function bind()
    {
        //return;
        $ssoAuthenticated   = $this->getAuthSource()->isAuthenticated();
        $identity           = $this->getIdentity() ?: '';
        $field              = $this->getIdentityField();

        if (!$ssoAuthenticated && $identity) {
            $this->clearIdentity();
        } elseif ($ssoAuthenticated && !$identity) {
            $profile = array();
            $attributes = $this->getAuthSource()->getAttributes();
            array_walk($attributes, function($data, $key) use (&$profile) {
                $profile[$key] = is_array($data) ? array_pop($data) : $data;
            });
            $identity = $profile[$field];
            $this->getStorage()->write($profile);
            Pi::service('user')->setPersist($profile);
        }
        Pi::service('user')->bind($identity, $field);

        return;
    }

    /**
     * Set storage
     *
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Get storage
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->loadStorage($this->options['storage']);
        }

        return $this->storage;
    }

    /**
     * Load authentication storage
     *
     * @param array $config
     * @return StorageInterface
     */
    public function loadStorage($config = array())
    {
        $class      = $config['class'];
        $options    = isset($config['options']) ? $config['options'] : array();
        $storage = new $class($options);

        return $storage;
    }

    /**
     * {@inheritDoc}
     */
    public function hasIdentity()
    {
        return $this->getIdentity() ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function clearIdentity()
    {
        $this->getStorage()->clear();
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($identity, $credential, $column = '')
    {
        throw new \Exception('Method is disabled.');
    }

    /**
     * {@inheritDoc}
     */
    public function requireLogin(array $params = array())
    {
        if (isset($params['redirect'])) {
            $return = $params['redirect'];
            unset($params['redirect']);
        } else {
            $return = Pi::service('url')->getRequestUri();
        }
        $params['ReturnTo'] = $return;
        $this->getAuthSource()->requireAuth($params);
    }

    /**
     * {@inheritDoc}
     */
    public function login(array $params = array())
    {
        if (isset($params['redirect'])) {
            $return = $params['redirect'];
            unset($params['redirect']);
        } else {
            $return = Pi::service('url')->getRequestUri();
        }
        $params['ReturnTo'] = $return;
        $this->getAuthSource()->login($params);
    }

    /**
     * {@inheritDoc}
     */
    public function logout(array $params = array())
    {
        if (isset($params['redirect'])) {
            $return = $params['redirect'];
            unset($params['redirect']);
        } else {
            $return = Pi::service('url')->getRequestUri();
        }
        $params['ReturnTo'] = $return;
        $this->getAuthSource()->logout($params);
    }

    /**
     * {@inheritDoc}
     */
    public function getData(array $fields = array())
    {
        $attributes = $this->getAuthSource()->getAttributes();
        foreach ($attributes as $key => $val) {
            if ($fields && !isset($fields[$key])) {
                continue;
            }
            if (is_array($val) && count($val) === 1) {
                $attributes[$key] = array_shift($val);
            }
        }

        return $attributes;
    }
}
