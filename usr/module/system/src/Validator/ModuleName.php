<?php
/**
 * Module name validator
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

namespace Module\System\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

class ModuleName extends AbstractValidator
{
    const RESERVED  = 'moduleNameReserved';
    const TAKEN     = 'moduleNameTaken';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::RESERVED  => 'Module name is reserved',
        self::TAKEN     => 'Module name is already taken',
    );

    protected $options = array(
        // Reserved module name which could be potentially conflicted with system
        'backlist'  => array('pi', 'zend', 'module', 'service', 'theme', 'application', 'event', 'registry', 'config'),
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
            $pattern = implode('|', $this->options['backlist']);
            if (preg_match('/(' . $pattern . ')/', $value)) {
                $this->error(static::RESERVED);
                return false;
            }
        }

        $where = array('name' => $value);
        if (!empty($context['id'])) {
            $where['id <> ?'] = $context['id'];
        }
        $rowset = Pi::model('module')->select($where);
        if ($rowset->count()) {
            $this->error(static::TAKEN);
            return false;
        }

        return true;
    }
}
