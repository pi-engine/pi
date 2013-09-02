<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Model\User\RowGateway;

use Pi;

/**
 * User account row gateway
 *
 * Update credential:
 *
 * ```
 *  $row = $accountModel->createRow($dataAarray);
 *  $row->prepare()->save();
 * ```
 *
 * Or
 * ```
 *  $row = $accountModel->createRow($dataAarray);
 *  $row->createSalt();
 *  $row->setCredential($raw_credential);
 *  $row->save();
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Account extends AbstractFieldRowGateway
{
    /**
     * {@inheritDoc}
     */
    protected function getMetaList()
    {
        return array();
    }

    /**
     * Get credential
     * @return string
     */
    public function getCredential()
    {
        return $this->offsetGet('credential');
    }

    /**
     * Setup credential with encrypt
     *
     * @param string $credential Raw credential
     * @return $this
     */
    public function setCredential($credential = null)
    {
        $credential = $credential ?: $this->offsetGet('credential');
        $credential = $this->transformCredential($credential);
        $this->offsetSet('credential', $credential);

        return $this;
    }

    /**
     * Create salt for credential hash
     *
     * @return $this
     */
    public function createSalt()
    {
        $this->offsetSet('salt', uniqid(mt_rand(), 1));

        return $this;
    }

    /**
     * Transform credential upon raw data
     *
     * @param string    $credential     Credential
     * @return string Encrypted credential value
     */
    public function transformCredential($credential)
    {
        $credential = md5(sprintf(
            '%s%s%s',
            $this->offsetGet('salt'),
            $credential,
            Pi::config('salt')
        ));

        return $credential;
    }

    /**
     * Prepare data for credential update: salt, encrypt credential
     *
     * @return $this
     */
    public function prepare()
    {
        $this->createSalt();
        $this->setCredential();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function save($rePopulate = true)
    {
        /*
        if (isset($this['credential'])) {
            $this->prepare();
        }
        */

        return parent::save($rePopulate);
    }
}
