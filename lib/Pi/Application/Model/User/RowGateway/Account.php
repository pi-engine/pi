<?php
/**
 * Pi User Account Model Row
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 * @subpackage      Model
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Model\User\RowGateway;
use Pi;
use Pi\Db\RowGateway\RowGateway;

/**
 * Update credential
 * <code>
 *  $row = $accountModel->createRow($dataAarray);
 *  $row->prepare()->save();
 * </code>
 *
 * Or
 * <code>
 *  $row = $accountModel->createRow($dataAarray);
 *  $row->createSalt();
 *  $row->setCredential($raw_credential);
 *  $row->save();
 * </code>
 */

class Account extends RowGateway
{
    public function getCredential()
    {
        return $this->offsetGet('credential');
    }

    public function setCredential($credential = null)
    {
        $credential = $credential ?: $this->offsetGet('credential');
        $credential = $this->transformCredential($credential);
        $this->offsetSet('credential', $credential);
        return $this;
    }

    /**
     * Create salt for credential hash
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
     * @return string treated credential value
     */
    public function transformCredential($credential)
    {
        $credential = md5(sprintf('%s%s%s', $this->offsetGet('salt'), $credential, Pi::config('salt')));
        return $credential;
    }

    /**
     * Prepare data for credential update: salt, encrypt credential
     *
     * @return  Account
     */
    public function prepare()
    {
        $this->createSalt();
        $this->setCredential();
        return $this;
    }
}
