<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * Page existence check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PageDuplicate extends AbstractValidator
{
    /** @var string */
    const PAGEEXISTS        = 'pageExists';

    /**
     * Message templates
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
     * @return bool
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

        $rowset = Pi::model('page')->select($where);
        if ($rowset->count()) {
            $this->error(static::PAGEEXISTS);
            return false;
        }

        return true;
    }
}
