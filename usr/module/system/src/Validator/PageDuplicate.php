<?php
/**
 * Page controller-action validator
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

class PageDuplicate extends AbstractValidator
{
    const PAGEEXISTS        = 'pageExists';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::PAGEEXISTS     => 'The page already exists',
    );

    /**
     * Page validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $where = array(
            'section'       => $context['section'],
            'module'        => $context['module'],
            'action'        => $context['action'],
            'controller'    => $value
        );
        /*
        if (empty($context['action']) || 'index' == $context['action']) {
            $where['action'] = array('', 'index');
        }
        if ('index' == $value) {
            $where['controller'] = array('', 'index');
        }
        */

        $rowset = Pi::model('page')->select($where);
        if ($rowset->count()) {
            $this->error(static::PAGEEXISTS);
            return false;
        }

        return true;
    }
}
