<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Authentication\Strategy;

/**
 * Authentication strategy
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class OAuth extends Local
{
    /**
     * {@inheritDoc}
     */
    protected $name = 'oauth';

    /**
     * {@inheritDoc}
     */
    public function authenticate($identity, $credential, $column = '')
    {
        $column     = 'email';
        $credential = '';

        $adapter = $this->getAdapter();
        $adapter->setIdentityColumn($column);
        $adapter->setIdentity($identity);
        $adapter->setCredential($credential);
        $result = $adapter->oAuthAuthenticate();

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $result->setData($adapter->getResultRow());
            $identity = $result->getData($this->getIdentityField());
            $this->getStorage()->write($identity);
        }

        return $result;
    }
}