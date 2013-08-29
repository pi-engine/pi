<?php
/**
 * User email validator
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
 * @since           3.0
 * @package         Module\System
 * @subpackage      Validator
 * @version         $Id$
 */

namespace Module\User\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * Validator user email
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class UserEmail extends AbstractValidator
{
    const RESERVED  = 'userEmailReserved';
    const USED      = 'userEmailUsed';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::RESERVED  => 'User email is reserved',
        self::USED      => 'User email is already used',
    );

    protected $options = array(
        'backlist'          => array(),
        'checkDuplication'  => true,
    );

    /**
     * User name validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        if (!empty($this->options['backlist'])) {
            $pattern = is_array($this->options['backlist']) ? implode('|', $this->options['backlist']) : $this->options['backlist'];
            if (preg_match('/(' . $pattern . ')/', $value)) {
                $this->error(static::RESERVED);
                return false;
            }
        }

        if ($this->options['checkDuplication']) {
            $where = array('email' => $value);
            if (!empty($context['id'])) {
                $where['id <> ?'] = $context['id'];
            }
            $rowset = Pi::model('account', 'user')->select($where);
            if ($rowset->count()) {
                $this->error(static::USED);
                return false;
            }
        }

        return true;
    }
}
