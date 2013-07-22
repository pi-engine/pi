<?php
/**
 * Pi User Account Model Row
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Model\User\RowGateway;

use Pi;
use Pi\Db\RowGateway\RowGateway;

/**
 * Update credential
 *
 * <code>
 *  $row = $accountModel->createRow($dataAarray);
 *  $row->prepare()->save();
 * </code>
 *
 * Or
 * <code>
 *
 *  $row = $accountModel->createRow($dataAarray);
 *  $row->createSalt();
 *  $row->setCredential($raw_credential);
 *  $row->save();
 * </code>
 */

class Account extends RowGateway
{
    /**
     * Get credential
     * @return string
     */
    public function getCredential()
    {
        return $this->offsetGet('credential');
    }

    /**
     * Setup edential with encrupt
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
     * @param string    $salt           Salt
     * @return string Encrypted credential value
     */
    public function transformCredential($credential)
    {
        $credential = md5(sprintf('%s%s%s', $this->offsetGet('salt'), $credential, Pi::config('salt')));
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
}
